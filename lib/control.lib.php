<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 控制器虚拟类															|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Object')) {
    require_once(sprintf('%s/object.lib.php', dirname(__FILE__)));
}

abstract class HA_Control_Abstract extends HA_Object
{

    /* {{{ 静态变量 */
    /**
     * @属性列表
     */
    protected static $_arrOpt = array(
        'theme' => 'default',
    );

    /**
     * @应用路径
     */
    protected static $_appDir;

    /* }}} */

    /* {{{ 成员变量 */

    /**
     * @变量列表
     */
    protected $_arrVal = array();

    /* }}} */

    /* {{{ public Boolean __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  Mixture $arrOpt
     * @return Boolean true
     */
    public function __construct($arrOpt = array())
    {
        $this->_init();
        return $this;
    }
    /* }}} */

    /* {{{ public Object  approot() */
    /**
     * 设置approot
     *
     * @access public
     * @return Object $this
     */
    public function approot($strDir)
    {
        self::$_appDir = self::realpath($strDir);
        return $this;
    }
    /* }}} */

    /* {{{ public Mixture excute() */
    /**
     * 页面展现
     *
     * @access public
     * @return Mixture
     */
    public function excute($strAct = null)
    {
        $mixRet = $this->_before_excute();
        if (is_object($mixRet)) {
            return $mixRet;
        }

        $objRes = HA_Context::instance();
        $strAct = empty($strAct) ? $objRes->get('action', 'index') : $strAct;
        $strAct = strtolower(trim($strAct));

        $action = sprintf('_action%s', ucfirst($strAct));
        if (!method_exists($this, $action)) {
            return $this->_undefined_action($strAct);
        }

        $mixRet = $this->$action();
        if (is_object($mixRet)) {
            return $mixRet;
        }
        $this->_after_excute();
        if (is_string($mixRet)) {
            echo $mixRet;
            return 0;
        }

        $strCtr = strtolower(array_pop(explode('_', get_class($this), 3)));
        $strObj = template($strAct, $strCtr, self::$_arrOpt['theme']);

        if (is_file($strObj)) {
            extract($this->_arrVal, EXTR_OVERWRITE);
            include($strObj);
        }

        return 0;
    }
    /* }}} */

    /* {{{ public Object  assign() */
    /**
     * 为输出页面分配数据
     *
     * @access public
     * @param  String  $strKey
     * @param  Mixture $mixVal
     * @return Object  $this
     */
    public function assign($strKey, $mixVal)
    {
        if (is_scalar($strKey)) {
            $this->_arrVal[trim($strKey)] = $mixVal;
        }

        return $this;
    }
    /* }}} */

    /* {{{ public String  url() */
    /**
     * 构造URL
     *
     * @access public
     * @param  String  $strAct
     * @param  Mixture $mixArg  (default null)
     * @param  String  $strCtl (default null)
     * @return String
     */
    public function url($strAct, $mixArg = null, $strCtl = null)
    {
        $strCtl = is_null($strCtl) ? get_class($this) : trim($strCtl);

        return $strRet;
    }
    /* }}} */

    /* {{{ public String  multipage() */
    /**
     * 计算分页链接
     *
     * @access public static
     */
    public static function multipage($intAll, $intPage, $intPerPage, $strURI)
    {
        $strURI .= strpos($strURI, '?') !== false ? '&' : '?';
        $intPages = max(1, ceil($intAll / $intPerPage));
        if ($intPage > $intPages) {
            header('Location: ' . $strURI . 'page=' . $intPages);
        }

        if ($intAll <= $intPerPage || $intPerPage == 0) {
            return '';
        }

        $intPageNum = 10;
        $intOffset = floor($intPageNum / 2);
        $intMinPage = ($intPage < $intOffset) ? 1 : $intPage - $intOffset + 1;
        $intMinPage = ($intPage + $intOffset > $intPages) ? $intPages - $intPageNum + 1 : $intMinPage;
        $intMinPage = $intMinPage > 0 ? $intMinPage : 1;
        $intMaxPage = $intMinPage + $intPageNum - 1;
        $intMaxPage = $intMaxPage <= $intPages ? $intMaxPage : $intPages;

        if ($intPage != 1) {
            $strPages = sprintf('<a href="' . $strURI . 'page=1">%s</a>&nbsp;', __('First Page'));
            $strPages.= sprintf('<a href="' . $strURI . 'page=' . ($intPage - 1) . '">%s</a>', __('Pre'));
        } else {
            $strPages = sprintf('%s&nbsp;%s', __('First Page'), __('Pre'));
        }

        for($i = $intMinPage; $i <= $intMaxPage; $i++) {
            if ($i == $intPage) {
                $strPages .= '&nbsp;<b>[&nbsp;' . $i . '&nbsp;]</b>';
            } else {
                $strPages .= '&nbsp;<a href="' .  $strURI . 'page=' . $i . '">[&nbsp;' . $i . '&nbsp;]</a>';
            }
        }
        if ($intPage != $intPages) {
            $strPages.= sprintf('&nbsp;<a href="' . $strURI . 'page=' . ($intPage + 1) . '">%s</a>&nbsp;', __('Next'));
            $strPages.= sprintf('<a href="' . $strURI . 'page=' . $intPages . '">%s</a>', __('Last Page'));
        } else {
            $strPages.= sprintf('&nbsp;%s&nbsp;%s', __('Next'), __('Last Page'));
        }
        $strPages.= sprintf('&nbsp;&nbsp;' . __('Total %d'), $intPages);

        return $strPages;
    }
    /* }}} */

    /* {{{ protected Boolean _loadModel() */
    /**
     * 加载Model文件
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _loadModel()
    {
        $arrArg = func_get_args();
        foreach ((array)$arrArg AS $model) {
            $file = sprintf('%s/model/%s.model.php', self::$_appDir, strtolower(trim($model)));
            if (!is_file($file)) {
                self::_error(8, sprintf('Error when loading model from "%s"', $file));
            } else {
                require_once($file);
            }
        }

        return true;
    }
    /* }}} */

    /* {{{ protected Boolean _before_excute() */
    /**
     * 执行前触发器
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _before_excute()
    {
    }
    /* }}} */

    /* {{{ protected Boolean _after_excute() */
    /**
     * 执行后触发器
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _after_excute()
    {
    }
    /* }}} */

    /* {{{ protected Boolean _actionIndex() */
    /**
     * 默认action
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _actionIndex()
    {
    }
    /* }}} */

    /* {{{ protected Boolean _undefined_action() */
    /**
     * 未定义的action处理方法
     *
     * @access protected
     * @param  String $strAct
     * @return Boolean true
     */
    protected function _undefined_action($strAct)
    {
        printf(__('Undefined action as "%s"'), $strAct);
        exit;
    }
    /* }}} */

}

