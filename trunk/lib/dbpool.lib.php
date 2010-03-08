<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +----------------------------------------------------------------------+
// | ���ݿ��	    		  								   		  	  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2006-2008 Baidu.com                                    |
// +----------------------------------------------------------------------+
// | Author: aleafs <zhangxuancheng@baidu.com>                        	  |
// +----------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Hapool')) {
    require_once(sprintf('%s/hapool.lib.php', dirname(__FILE__)));
}

class HA_Dbpool extends HA_Hapool
{

    /* {{{ ��̬���� */

    /**
     * @�����б�
     */
    private static $_arrServers = array();

    /* }}} */

    /* {{{ ��Ա���� */

    /**
     * @������ô���
     */
    private $_intTransCnt = 0;

    /**
     * @�ַ���
     */
    private $_strCharset;

    /**
     * @����
     */
    private $_objRes;

    /* }}} */

    /* {{{ public Object  instance() */
    /**
     * ��ȡ���������һ��ʵ��
     *
     * @access public static
     * @param  Mixture $mixArg
     * @param  String  $strCls (default null)
     * @return Object
     */
    public static function instance($mixArg, $strCls = null)
    {
        return parent::instance($mixArg, is_null($strCls) ? __CLASS__ : $strCls);
    }
    /* }}} */

    /* {{{ public Boolean register() */
    /**
     * ע��һ�����ݿ������
     *
     * @access public static
     * @param  String $strIdx
     * @param  String $strPro
     * @param  arrDns $arrDns
     * @return Boolean true or false
     */
    public static function register($strIdx, $strPro, $arrDns)
    {
        $strIdx = strtolower(trim($strIdx));
        if (isset(self::$_arrServers[$strIdx])) {
            return false;
        }

        $strPro = strtolower(trim($strPro));
        $strCls = sprintf('HA_Db_%s', ucfirst($strPro));
        if (!class_exists($strCls)) {
            return false;
        }

        if (self::_verify_dns($arrDns) !== true) {
            return false;
        }
        self::$_arrServers[$strIdx] = array(
            'class' => $strCls,
            'dns'   => $arrDns,
        );

        return true;
    }
    /* }}} */

    /* {{{ public Mixture __call() */
    /**
     * ������������
     *
     * @access public
     * @param  String  $method
     * @param  Mixture $mixArg
     * @return Mixture
     */
    public function __call($method, $mixArg)
    {
        if (!method_exists($this->_objRes, $method)) {
            self::_error(2, sprintf(
                'Undefined method "%s" for class %s or %s',
                $method, get_class($this), get_class($this->_objRes)
            ), true);
            return false;
        }

        return call_user_func_array(array(&$this->_objRes, $method), $mixArg);
    }
    /* }}} */

    /* {{{ public Mixture query() */
    /**
     * ִ��һ��SQL
     *
     * @access public
     * @param  Mixture $mixSql
     * @param  Boolean $bolIgn (default false)
     * @param  Mixture
     */
    public function query($mixSql, $bolIgn = false)
    {
        if (!is_string($mixSql) && !is_array($mixSql)) {
            return false;
        }

        if (is_array($mixSql)) {
            $bolErr = false;
            $this->begin();
            foreach ($mixSql AS $strSql) {
                if ($this->query($strSql, $bolIgn) === false) {
                    $bolErr = true;
                    break;
                }
            }
            if ($bolErr === true) {
                $this->rollback();
            } else {
                $this->commit();
            }

            $mixRet = $bolErr === true ? false : true;
        } else {

            if (empty($mixSql)) {
                return true;
            }

            if (self::_isManip($mixSql) && $this->_connectToMaster() !== true) {
                return false;
            }

            if ($this->_connectToSlave() !== true) {
                return false;
            }
            $mixRet = $this->_objRes->query(trim($mixSql));
            if ($bolIgn !== true) {
                $this->_intQueries++;
            }
            if ($mixRet === false) {
                self::_error(7, sprintf('Error occurred for query "%s"', $mixSql));
            }
        }

        return $mixRet;
    }
    /* }}} */

    /* {{{ public Mixture getOne() */
    /**
     * ��ȡһ�����
     *
     * @access public
     * @param  String $strSql
     * @return Mixture
     */
    public function getOne($strSql)
    {
        if (!is_resource($strSql)) {
            if (!is_string($strSql) || empty($strSql) || self::_isManip($strSql)) {
                return false;
            }

            if (false === ($strSql = $this->query($strSql))) {
                return false;
            }
        }

        if ($this->rows($strSql) == 0) {
            return null;
        }

        return array_shift($this->fetch($strSql));
    }
    /* }}} */

    /* {{{ public Mixture getAll() */
    /**
     * ��ȡһ�β�ѯ�����н��
     *
     * @access public
     * @param  Mixture $mixRes
     * @return Mixture
     */
    public function getAll($mixRes)
    {
        $mixRet = array();
        if (!is_resource($mixRes)) {
            if (!is_string($mixRes) ||  empty($mixRes) || self::_isManip($mixRes)) {
                return false;
            }

            if (false === ($mixRes = $this->query($mixRes))) {
                return false;
            }
        }

        while ($row = $this->fetch($mixRes)) {
            if (!is_array($row)) {
                return false;
            }
            $mixRet[] = $row;
        }
        $this->free($mixRes);

        return $mixRet;
    }
    /* }}} */

    /* {{{ public Boolean begin() */
    /**
     * ��ʼһ������
     *
     * @access public
     * @return Boolean true or false
     */
    public function begin()
    {
        if ($this->_connectToMaster() !== true) {
            return false;
        }

        if ($this->_intTransCnt == 0) {
            $this->_objRes->begin();
        }
        $this->_intTransCnt++;

        return true;
    }
    /* }}} */

    /* {{{ public Boolean commit() */
    /**
     * �ύһ������
     *
     * @access public
     * @return Boolean true or false
     */
    public function commit()
    {
        if ($this->_intTransCnt > 0) {
            $this->_objRes->commit();
            $this->_intTransCnt = 0;
        }

        return true;
    }
    /* }}} */

    /* {{{ public Boolean rollback() */
    /**
     * �ع�һ������
     *
     * @access public
     * @return Boolean true or false
     */
    public function rollback()
    {
        if ($this->_intTransCnt > 0) {
            $this->_objRes->rollback();
            $this->_intTransCnt = 0;
        }

        return true;
    }
    /* }}} */

    /* {{{ public String  escape() */
    /**
     * SQL��ȫ���˺���
     *
     * @access public
     * @param  String $strVal
     * @return String
     */
    public function escape($strVal)
    {
        if (is_array($strVal)) {
            $mixRet = array();
            foreach ($strVal AS $strKey => $mixTmp) {
                $mixRet[$this->escape($strKey)] = $this->escape($mixTmp);
            }

            return $mixRet;
        }
        $this->_connectToSlave();

        return $this->_objRes->escape($strVal);
    }
    /* }}} */

    /* {{{ public String  version() */
    /**
     * ����MySQL�汾��
     *
     * @access public
     * @param  Integer $intDep
     * @return String
     */
    public function version($intDep = null)
    {
        if (method_exists($this->_objRes, 'version')) {
            $strVer = $this->_objRes->version();
        } else {
            $strVer = 'unknown';
        }

        if (is_numeric($intDep)) {
            $intDep = min(1, max(4, intval($intDep)));
            $intPos = strpos($strVer, '.', $intDep - 1);
            if ($intPos !== false) {
                return substr($strVer, 0, $intPos);
            }
        }

        return $strVer;
    }
    /* }}} */

    /* {{{ protected Object  _options() */
    /**
     * ���ö�������
     *
     * @access protected
     * @param  Mixture $mixArg (default null)
     * @return Object $this
     */
    protected function _options($mixArg = null)
    {
        $arrOpt = self::$_arrServers[$this->_strIdx];
        if (empty($arrOpt)) {
            self::_error(1, sprintf('Undefined dbpool index "%s"', $this->_strIdx), true);
            return $this;
        }

        parent::_options($arrOpt['dns']);
        $this->_objRes = new $arrOpt['class']();

        return $this;
    }
    /* }}} */

    /* {{{ protected Boolean _connect() */
    /**
     * ���ӷ���
     *
     * @access protected
     * @param  String  $strHost
     * @param  Integer $intPort
     * @param  String  $strUser
     * @param  String  $strPass
     * @return Boolean true or false
     */
    protected function _connect($strHost, $intPort, $strUser, $strPass = '')
    {
        if (false === ($res = $this->_objRes->connect($strHost, $intPort, $strUser, $strPass))) {
            self::_error(4, sprintf(
                'Error to onnect to %s:%d identified by %s (password ***)',
                $strHost, $intPort, $strUser
            ), true);
            return false;
        }
        $this->_resHandle = $res;

        if (strlen($this->_strCharset) > 0) {
            $this->_objRes->charset($this->_strCharset);
        }

        return true;
    }
    /* }}} */

    /* {{{ private Boolean _disconnect() */
    /**
     * �ر�����
     *
     * @access private
     * @return Boolean true
     */
    private function _disconnect()
    {
        if ($this->_objRes->disconnect()) {
            $this->_resHandle = null;
        }

        return true;
    }
    /* }}} */

    /* {{{ private Boolean _isManip() */
    /**
     * �ж�һ��SQL�Ƿ�дSQL
     *
     * @access private static
     * @param  String $strSql
     * @return Boolean true or false
     */
    private static function _isManip($strSql)
    {
        $strVal = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|ALTER|GRANT|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $strVal . ')\s+/i', $strSql)) {
            return true;
        }

        return false;
    }
    /* }}} */

    /* {{{ private Boolean _verify_dns() */
    /**
     * У�鴫���DNS�����Ƿ���ȷ
     *
     * @access private static
     * @param [in/out] $arrDns   : &
     * @return Boolean true or false
     */
    private static function _verify_dns(&$arrDns)
    {
        if (is_string($arrDns)) {
            $arrTmp = parse_url($arrDns);
            if (!is_array($arrTmp) || empty($arrTmp['host'])) {
                return false;
            }
            $arrDns = array_map('rawurldecode', $arrTmp);
            return true;
        }

        if (!is_array($arrDns)) {
            return false;
        }

        foreach (array('host', 'port', 'user', 'pass', 'path') AS $strKey) {
            $arrDns[$strKey] = trim($arrDns[$strKey]);
        }
        $bolErr = true;
        if (empty($arrDns['host'])) {
            $arrSlv = array();
            $arrTmp = (array)$arrDns['slave'];
            foreach ($arrTmp AS $arrSub) {
                if (self::_verify_dns($arrSub)) {
                    $arrSlv[] = $arrSub;
                    $bolErr = false;
                }
            }
            $arrDns['slave'] = $arrSlv;
        }

        return !$bolErr;
    }
    /* }}} */

}

/**
 * @MySQL������
 */
class HA_Db_Mysql extends HA_Object
{

    /* {{{ ��Ա���� */

    /**
     * @���ӷ�
     */
    private $_resHandle;

    /**
     * @�汾��
     */
    private $_strVersion;

    /* }}} */

    /* {{{ public Mixture query() */
    /**
     * ִ��query ����
     *
     * @access public
     * @param  String $query
     * @return Mixture
     */
    public function query($query)
    {
        return mysql_query(trim($query), $this->_resHandle);
    }
    /* }}} */

    /* {{{ public Mixture connect() */
    /**
     * ����
     *
     * @param  String  $strHost
     * @param  Integer $intPort
     * @param  String  $strUser
     * @param  String  $strPass
     * @return Resource or Boolean false
     */
    public function connect($strHost, $intPort, $strUser, $strPass = '')
    {
        $intPort = is_numeric($intPort) && $intPort > 0 ? intval($intPort) : 3306;
        if (false === ($res = @mysql_connect(sprintf('%s:%d', $strHost, $intPort), $strUser, $strPass))) {
            return false;
        }
        $this->_resHandle   = $res;

        return $this->_resHandle;
    }
    /* }}} */

    /* {{{ public Boolean charset() */
    /**
     * �����ַ���
     *
     * @access public
     * @param  String $charset
     * @return Boolean true or false
     */
    public function charset($charset)
    {
        if (version_compare($this->version(), '4.1.0.0') < 0) {
            return false;
        }
        $this->query(sprintf('SET NAMES %s', $this->escape($charset)));

        return true;
    }
    /* }}} */

    /* {{{ public String  version() */
    /**
     * ��ȡMySQL�汾��
     *
     * @access public
     * @return String
     */
    public function version()
    {
        if (empty($this->_strVersion)) {

        }

        return $this->_strVersion;
    }
    /* }}} */

    /* {{{ public String  escape() */
    /**
     * SQLע��ת��
     *
     * @access public
     * @param  String $strVal
     * @return String
     */
    public function escape($strVal)
    {
        if (is_resource($this->_resHandle)) {
            return mysql_real_escape_string($strVal, $this->_resHandle);
        }

        return mysql_real_escape_string($strVal);
    }
    /* }}} */

    /* {{{ public Mixture fetch() */
    /**
     * ��ȡ��ѯ��¼
     *
     * @access public
     * @param  Resource $res
     * @param  Integer $mode
     * @return Mixture $data or Boolean false
     */
    public function fetch($res, $mode = MYSQL_ASSOC)
    {
        if (!is_resource($res)) {
            return false;
        }

        if ($mode == MYSQL_NUM) {
            return mysql_fetch_row($res);
        }

        return mysql_fetch_assoc($res);
    }
    /* }}} */

    /* {{{ public Integer rows() */
    /**
     * ���ؽ��������
     *
     * @access public
     * @param  Resource $res
     * @return Integer
     */
    public function rows($res)
    {
        if (!is_resource($res)) {
            return false;
        }

        return mysql_num_rows($res);
    }
    /* }}} */

    /* {{{ public Integer affected() */
    /**
     * ��ȡ���µ�����
     *
     * @access public
     * @return Integer
     */
    public function affected()
    {
        return mysql_affected_rows($this->_resHandle);
    }
    /* }}} */

    /* {{{ public Boolean free() */
    /**
     * �ͷŲ�ѯ�����
     *
     * @access public
     * @param  Resource $res
     * @return Boolean true or false
     */
    public function free($res)
    {
        if (!is_resource($res)) {
            return false;
        }

        return mysql_free_result($res);
    }
    /* }}} */

    /* {{{ public Boolean begin() */
    /**
     * ��ʼһ������
     *
     * @access public
     * @return Boolean true or false
     */
    public function begin()
    {
        $this->query('SET AUTOCOMMIT=0');
        $this->query('BEGIN');

        return true;
    }
    /* }}} */

    /* {{{ public Boolean commit() */
    /**
     * �ύһ������
     *
     * @access public
     * @return Boolean true or false
     */
    public function commit()
    {
        $this->query('COMMIT');
        $this->query('SET AUTOCOMMIT=1');

        return true;
    }
    /* }}} */

    /* {{{ public Boolean rollback() */
    /**
     * �ع�һ������
     *
     * @access public
     * @return Boolean true or false
     */
    public function rollback()
    {
        $this->query('ROLLBACK');
        $this->query('SET AUTOCOMMIT=1');

        return true;
    }
    /* }}} */

    /* {{{ public Integer lastId() */
    /**
     * ��ȡ����SQL�����Ĳ���ID
     *
     * @access public
     * @return Integer or Boolean false
     */
    public function lastId()
    {
        return mysql_insert_id($this->_resHandle);
    }
    /* }}} */

    /* {{{ public Mixture field() */
    /**
     * ��ȡ��ṹ
     *
     * @access public
     * @param  String $strTab
     * @return Mixture
     */
    public function field($strTab)
    {
        $mixRet = array();
        $strSql = sprintf('SHOW COLUMNS FROM %s', $strTab);
        $objRes = $this->query($strSql);
        if (!is_resource($objRes)) {
            return false;
        }

        while ($row = $this->fetch($objRes)) {
            if (strpos($row['Field'], '`') !== false) {
                $row['Field']   = str_replace('`', '', $row['Field']);
            }

            $bolAutoKid = false;
            if ($bolAutoKid === false && stripos($row['Extra'], 'auto_increment') !== false) {
                $bolAutoKid = true;
            }
            $mixRet[$row['Field']] = array(
                'type'  => $row['Type'],
                'uniq'  => preg_match('/(PRI|UNI)/is', $row['Key']) ? true : false,
                'auto'  => $bolAutoKid,
                'empty' => $row['Default'],
            );
        }

        return $mixRet;
    }
    /* }}} */

    /* {{{ public Mixture index() */
    /**
     * ����������
     *
     * @access public
     * @param  String $strTab
     * @return Mixture or Boolean false
     */
    public function index($strTab)
    {
        $strSql = sprintf('SHOW INDEX FROM %s', $strTab);
        if (false === ($res = $this->query($strSql, true))) {
            return false;
        }

        $mixRet = array();
        while ($row = $this->fetch($res)) {
            $arrTmp = &$mixRet[$row['Key_name']];
            if (is_null($arrTmp)) {
                $arrTmp = array(
                    'uniq'  => false,
                    'cols'  => array(),
                );
            }

            $arrTmp['uniq'] = $row['Non_unique'] > 0 ? false : true;
            $arrTmp['cols'][$row['Seq_in_index']] = $row['Column_name'];
        }

        foreach ($mixRet AS $strKey => &$arrVal) {
            ksort($arrVal['cols']);
        }

        return $mixRet;
    }
    /* }}} */

    /* {{{ public Boolean disconnect() */
    /**
     * �ر�����
     *
     * @access public
     * @return Boolean true
     */
    public function disconnect()
    {
        if (is_resource($this->_resHandle)) {
            mysql_close($this->_resHandle);
        }
        $this->_resHandle = null;

        return true;
    }
    /* }}} */

}

