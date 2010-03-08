<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | ���ӹ�����																|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Aleafs. All Rights Reserved							|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Single')) {
    require_once(sprintf('%s/single.lib.php', dirname(__FILE__)));
}

/**
 * @���ӹ�����
 */
class HA_Hapool extends HA_Single
{

    /* {{{ ��Ա���� */

    /**
     * @�Ƿ�������
     */
    protected $_bolIsMaster = false;

    /**
     * @���Ӿ��
     */
    protected $_resHandle;

    /**
     * @ͨѶ����
     */
    protected $_intQueries  = 0;

    /**
     * @�����Ӵ���
     */
    protected $_intCnLimit  = 10;

    /**
     * @������ַ
     */
    protected $_strHostAddr;

    /**
     * @�����˿�
     */
    protected $_intHostPort;

    /**
     * @�û���
     */
    protected $_strUserName;

    /**
     * @����
     */
    protected $_strPassWord;

    /**
     * @�ӷ�����
     */
    protected $_mixSlave;

    /* }}} */

    /* {{{ public Object instance() */
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

    /* {{{ public Boolean __destruct() */
    /**
     * ��������
     *
     * @access public
     * @return Boolean true or false
     */
    public function __destruct()
    {
        if (method_exists($this, '_disconnect')) {
            $this->_disconnect();
        }
        $this->_resHandle   = null;

        return parent::__destruct();
    }
    /* }}} */

    /* {{{ public Resource handle() */
    /**
     * ��ȡ���ӷ�
     *
     * @access public
     * @return Resource
     */
    public function handle()
    {
        return $this->_resHandle;
    }
    /* }}} */

    /* {{{ public Mixture  random() */
    /**
     * ���ѡȡһ̨������
     *
     * @access public static
     * @param  Array $arrAddr
     * @return Mixture or Boolean false
     */
    public static function random($arrAddr)
    {
        if (!is_array($arrAddr)) {
            return false;
        }

        $intIdx = 0;
        $intMax = 0;
        $arrTmp = array();

        foreach ($arrAddr AS $mixVal) {
            $intMax += isset($mixVal['weight']) ? (int)$mixVal['weight'] : 1;
            $arrTmp[] = $intMax;
        }

        $intKey = rand(0, $intMax - 1);
        foreach ($arrTmp AS $key => $val) {
            if ($val > $intKey) {
                $intIdx = $key;
                break;
            }
        }

        return $arrAddr[$intIdx];
    }
    /* }}} */

    /* {{{ protected String  _index() */
    /**
     * ��ȡ���ӵ�idx
     *
     * @access protected static
     * @param  Mixture $mixArg (refferrence)
     * @return String
     */
    protected static function _index(&$mixArg)
    {
        if (is_string($mixArg) === true) {
            $mixArg = parse_url($mixArg);
            $mixArg['user'] = rawurldecode($mixArg['user']);
            $mixArg['pass'] = rawurldecode($mixArg['pass']);
            $mixArg['path'] = rawurldecode($mixArg['path']);
        }

        if (!is_array($mixArg) || !isset($mixArg['host'])) {
            return false;
        }

        return md5(sprintf(
            "%s\t%d\t%s", $mixArg['host'], $mixArg['port'], $mixArg['user']
        ));
    }
    /* }}} */

    /* {{{ protected Boolean _connectToMaster() */
    /**
     * ǿ������MASTER������
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _connectToMaster()
    {
        if ($this->_bolIsMaster === true)
        {
            return true;
        }

        while ($this->_intCnLimit > 0)
        {
            if ($this->_connect($this->_strHostAddr, $this->_intHostPort, $this->_strUserName, $this->_strPassWord) === true)
            {
                $this->_bolIsMaster = true;
                return true;
            }
            $this->_intCnLimit--;
            usleep(20000);
        }

        return false;
    }
    /* }}} */

    /* {{{ protected Boolean _connectToSlave() */
    /**
     * ǿ������SLAVE������
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _connectToSlave()
    {
        if (is_resource($this->_resHandle))
        {
            return true;
        }

        $mixTmp = self::random($this->_mixSlave);
        if (!is_array($mixTmp) || empty($mixTmp))
        {
            return $this->_connectToMaster();
        }

        if ($this->_intCnLimit < 1)
        {
            $this->_error(1, 'Max Connection Try!');
            return false;
        }

        if ($this->_connect($mixTmp['host'], $mixTmp['port'], $mixTmp['user'], $mixTmp['pass']) === true)
        {
            return true;
        }

        foreach ($this->_mixSlave AS $mixTmp)
        {
            if ($this->_connect($mixTmp['host'], $mixTmp['port'], $mixTmp['user'], $mixTmp['pass']) === true)
            {
                return true;
            }
        }
        $this->_intCnLimit--;

        return false;
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
    protected function _connect($strHost, $intPort, $strUser, $strPass)
    {
        return true;
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
        $this->_bolIsMaster = false;
        $this->_resHandle   = null;
        $this->_strHostAddr = trim($mixArg['host']);
        $this->_intHostPort = (int)$mixArg['port'];
        $this->_strUserName = trim($mixArg['user']);
        $this->_strPassWord = trim($mixArg['pass']);
        $this->_mixSlave    = $mixArg['slave'];

        return $this;
    }
    /* }}} */

}
