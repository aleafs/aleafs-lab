<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 单例模式基类	    					    							|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$
//

if (!class_exists('HA_Object')) {
    require_once(sprintf('%s/object.lib.php', dirname(__FILE__)));
}

/**
 * @单例模式基类
 */
class HA_Single extends HA_Object
{

    /* {{{ 成员变量 */

    /**
     * @实例数组
     */
    private static $_arrObj = array();

    /**
     * @对象索引
     */
    protected $_strIdx;

    /* }}} */

    /* {{{ public Object instance() */
    /**
     * 获取单例对象的一个实例
     *
     * @access public static
     * @param  Mixture $mixArg
     * @param  String  $strCls (default null)
     * @return Object
     */
    public static function instance($mixArg, $strCls = null)
    {
        $strCls = is_string($strCls) ? trim($strCls) : self::getclass(__CLASS__);
        $strIdx = call_user_func(array($strCls, '_index'), $mixArg);
        if (!is_scalar($strIdx) && !is_resource($strIdx)) {
            $this->_error(1, __('Undefined class %s or method _index for singleton', $strCls), true);
        }

        $objVal = &self::$_arrObj[$strCls][$strIdx];
        if (!is_object($objVal)) {
            $objVal = new $strCls($strIdx, $mixArg);
        }

        return $objVal;
    }
    /* }}} */

    /* {{{ public Boolean __destruct() */
    /**
     * 析构函数
     *
     * @access public
     * @return Boolean true
     */
    public function __destruct()
    {
        //TODO: core dump
        //unset(self::$_arrObj[get_class($this)][$this->_strIdx]);
        return true;
    }
    /* }}} */

    /* {{{ protected Object __construct() */
    /**
     * 受保护的构造函数
     *
     * @access protected
     * @param  String  $strIdx
     * @param  Mixture $mixArg (default null)
     * @return Object $this
     */
    protected function __construct($strIdx, $mixArg = null)
    {
        $this->_options($mixArg)->_init();
        $this->_strIdx = $strIdx;
        return $this;
    }
    /* }}} */

    /* {{{ protected Object _options() */
    /**
     * 设置对象属性
     *
     * @access protected
     * @param  Mixture $mixArg (default null)
     * @return Object $this
     */
    protected function _options($mixArg = null)
    {
        return $this;
    }
    /* }}} */

    /* {{{ protected String  _index() */
    /**
     * 构造单例模式对象索引
     *
     * @access protected static
     * @return String
     */
    protected static function _index()
    {
        $arrArg = func_get_args();
        if (is_scalar($arrArg[0])) {
            return $arrArg[0];
        }

        return 'default';
    }
    /* }}} */

    /* {{{ protected Mixture _brother() */
    /**
     * 获取同类兄弟实例
     *
     * @access public
     * @return Mixture
     */
    protected function _brother()
    {
        return self::$_arrObj[get_class($this)];
    }
    /* }}} */

    /* {{{ protected Boolean _alias() */
    /**
     * 为当前对象创建别名
     *
     * @access protected
     * @param  String $strIdx
     * @return Boolean true or false
     */
    protected function _alias($strIdx)
    {
        $strIdx = strtolower(trim($strIdx));
        $strCls = get_class($this);
        if (is_object(self::$_arrObj[$strCls][$strIdx])) {
            return false;
        }
        self::$_arrObj[$strCls][$strIdx] = &$this;

        return true;
    }
    /* }}} */

}

