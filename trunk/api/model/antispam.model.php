<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | ����������ģ��															|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Aleafs. All Rights Reserved							|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id$

class App_Model_Antispam extends HA_Single
{

    /* {{{ ��̬���� */

    /**
     * @����
     */
    private static $_strDb  = 'aleafs_api';

    /**
     * @GET�������
     */
    private static $_arrGet = array('ck', 'ln', 'rf', 'sw');

    /* }}} */

    /* {{{ ��Ա���� */

    /**
     * @����
     */
    private $_arrOpt;

    /**
     * @��·���
     */
    private $_bolErr;

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
    public static function instance($mixArg = '', $strCls = null)
    {
        return parent::instance($mixArg, is_null($strCls) ? __CLASS__ : $strCls);
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * ��ȡ�����ĳ������
     *
     * @access public
     * @param  String $strKey
     * @return Mixture
     */
    public function get($strKey)
    {
        if ($this->_bolErr === false) {
            return null;
        }

        if (empty($this->_arrOpt) && $this->_load() !== true) {
            $this->_bolErr = true;
            return null;
        }

        return $this->_arrOpt[strtolower(trim($strKey))];
    }
    /* }}} */

    /* {{{ public Object  api() */
    /**
     * ��ȡ��Ӧ��API����
     *
     * @access public
     * @return Object
     */
    public function api()
    {
        return App_Model_Api::instance($this->get('api_id'));
    }
    /* }}} */

    /* {{{ public Mixture session() */
    /**
     * д��session
     *
     * @access public static
     * @return Boolean true or false
     */
    public static function session()
    {
        $intNow = time();
        $objRes = HA_Context::instance();
        $objDao = HA_Dbpool::instance('api');
        $arrVal = array(
            'sessid'    => md5(uniqid(rand(), true)),
            'idname'    => $objRes->idname(),
            'userip'    => self::userip(true),
            'expire'    => $intNow + HA_Config::instance()->get('session.expire', 3600),
            'gmtime'    => $intNow,
            'cltime'    => (int)$objRes->get('tm', $intNow),
            'tmzone'    => (int)$objRes->get('tz', 0),
            'islock'    => 0,
            'sessval'   => $objDao->escape(self::_query()),
        );

        $strSql = sprintf(
            "INSERT INTO %s.as_session (%s) VALUES ('%s')",
            self::$_strDb, implode(',', array_keys($arrVal)), implode("','", $arrVal)
        );
        if ($objDao->query($strSql) === false) {
            return false;
        }

        return $arrVal['sessid'];
    }
    /* }}} */

    /* {{{ public Boolean __destruct() */
    /**
     * ��������
     *
     * @access public
     * @return Boolean true or false
     */
    public function __destruct()
    {
        if (rand(1, 100) <= HA_Config::instance()->get('session.gc_probability', 10)) {
            HA_Dbpool::instance('api')->query(sprintf(
                'DELETE FROM %s.as_session WHERE expire < %d AND islock = 0',
                self::$_strDb, time()
            ));
        }

        return parent::__destruct();
    }
    /* }}} */

    /* {{{ private Boolean _load() */
    /**
     * ��������
     *
     * @access private
     * @return Boolean true or false
     */
    private function _load()
    {
        $objRes = HA_Dbpool::instance('api');
        $strSql = sprintf(
            "SELECT * FROM %s.as_sitelist WHERE %s='%s' LIMIT 1",
            self::$_strDb, is_numeric($this->_strIdx) ? 'api_id' : 'dmsign',
            $objRes->escape($this->_strIdx)
        );
        $arrOpt = $objRes->fetch($objRes->query($strSql));
        if (!is_array($arrOpt) || empty($arrOpt)) {
            return false;
        }

        $this->_arrOpt = $arrOpt;
        self::$_arrCol = array_keys($arrOpt);
        if (is_numeric($this->_strIdx)) {
            $this->_alias($arrOpt['dmsign']);
        } else {
            $this->_alias($arrOpt['api_id']);
        }

        return true;
    }
    /* }}} */

    /* {{{ private String  _domain() */
    /**
     * ��URL�л�ȡ����
     *
     * @access private static
     * @param  String $strUrl
     * @return String
     */
    private static function _domain($strUrl)
    {
        $arrUrl = parse_url($strUrl);
        $strUrl = empty($arrUrl['host']) ? $arrUrl['path'] : $arrUrl['host'];

        return strtolower(trim($strUrl));
    }
    /* }}} */

    /* {{{ private String  _query() */
    /**
     * ��ȡGET����
     *
     * @access private static
     * @return String
     */
    private static function _query()
    {
        $arrQry = array();
        $objRes = HA_Context::instance();
        foreach (self::$_arrGet AS $strKey) {
            $arrQry[] = sprintf('%s=%s', $strKey, urlencode($objRes->get($strKey, '')));
        }

        return implode('&', $arrQry);
    }
    /* }}} */

    /* {{{ protected String  _index() */
    /**
     * ���쵥��ģʽ��������
     *
     * @access protected static
     * @return String
     */
    protected static function _index()
    {
        $arrArg = func_get_args();
        $strIdx = $arrArg[0];

        if (!is_scalar($strIdx)) {
            return '';
        }

        $strIdx = trim($strIdx);
        if (is_numeric($strIdx)) {
            return $strIdx;
        }
        if (preg_match('/^[a-z0-9]{32}$/is', $strIdx)) {
            return strtolower($strIdx);
        }

        $strIdx = self::_domain($strIdx);
        if (empty($strIdx)) {
            return '';
        }

        return strtolower(md5($strIdx));
    }
    /* }}} */

}

