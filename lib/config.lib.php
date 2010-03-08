<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 配置读取类																|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Single')) {
    require_once(sprintf('%s/single.lib.php', dirname(__FILE__)));
}

class HA_Config extends HA_Single
{

    /* {{{ 成员变量 */

    /**
     * @配置项
     */
    private $_arrOpt = array();

    /**
     * @是否已经加载
     */
    private $_isload = false;

    /* }}} */

    /* {{{ 静态变量 */

    /**
     * @注册的资源
     */
    private static $_arrRes = array();

    /* }}} */

    /* {{{ public static Object  instance() */
    /**
     * 获取单例对象的一个实例
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

    /* {{{ public static Boolean bind() */
    /**
     * 注册配置资源
     *
     * @access public static
     * @param  String $strKey
     * @param  String $strPro
     * @param  Mixture $mixOpt
     * @return Boolean true or false
     */
    public static function bind($strKey, $strPro, $mixOpt)
    {
        $strKey = strtolower(trim($strKey));
        $strPro = strtolower(trim($strPro));
        if (isset(self::$_arrRes[$strKey])) {
            self::_error(100, sprintf('Defined configure resource "%s" ', $strKey));
            return false;
        }

        $strCls = sprintf('HA_Config_Parser_%s', ucfirst($strPro));
        if (!class_exists($strCls)) {
            self::_error(101, sprintf('Undefined configure parser "%s"', $strPro));
            return false;
        }
        self::$_arrRes[$strKey] = array(
            'class'  => $strCls,
            'option' => $mixOpt,
        );

        return true;
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 获取配置项值
     *
     * @access public
     * @param  String  $strKey
     * @param  Mixture $default = null
     * @return Mixture
     */
    public function get($strKey = '', $default = null)
    {
        if ($this->_isload !== true) {
            if ($this->_load() !== true) {
                return null;
            }
            $this->_isload = true;
        }

        $strKey = strtolower(trim($strKey));
        if (isset($this->_arrOpt[$strKey])) {
            return $this->_arrOpt[$strKey];
        }

        return $default;
    }
    /* }}} */

    /* {{{ protected Boolean _load() */
    /**
     * 加载配置文件
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _load()
    {
        $mixArg = self::$_arrRes[strtolower($this->_strIdx)];
        if (!is_array($mixArg) || empty($mixArg['class'])) {
            self::_error(103, sprintf('Undefined configure resource "%s"', $this->_strIdx));
            return false;
        }

        $strCls = $mixArg['class'];
        $objRes = new $strCls($mixArg['option']);
        if (!method_exists($objRes, 'parse') || false === ($arrRet = $objRes->parse())) {
            self::_error(104, sprintf('Configure resource "%s" parse errror', $this->_strIdx));
            return false;
        }
        $this->_arrOpt = array_change_key_case($arrRet, CASE_LOWER);

        return true;
    }
    /* }}} */

}

class HA_Config_Parser_Ini extends HA_Object
{

    /* {{{ 成员变量 */
    /**
     * @INI文件路径
     */
    private $_strRes;

    /* }}} */

    /* {{{ public Boolean __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String $strRes
     * @return Boolean true
     */
    public function __construct($strRes = '')
    {
        $this->_strRes = self::realpath($strRes);
        return true;
    }
    /* }}} */

    /* {{{ public Mixture parse() */
    /**
     * 解析配置数据
     *
     * @access public
     * @return Mixture
     */
    public function parse()
    {
        return parse_ini_file($this->_strRes, false);
    }
    /* }}} */

}
