<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 统一入口程序															|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$


if (!defined('HA_LIB_PATH')) {
    define('HA_LIB_PATH',   dirname(__FILE__));
}

if (!defined('HA_APP_ROOT')) {
    define('HA_APP_ROOT',   dirname(dirname(realpath($_SERVER['SCRIPT_FILENAME']))));
}

require_once(HA_LIB_PATH . '/single.lib.php');
require_once(HA_LIB_PATH . '/config.lib.php');
require_once(HA_LIB_PATH . '/context.lib.php');
require_once(HA_LIB_PATH . '/control.lib.php');
require_once(HA_LIB_PATH . '/dbpool.lib.php');
require_once(HA_LIB_PATH . '/filelog.lib.php');
require_once(HA_LIB_PATH . '/gettext.lib.php');
require_once(HA_LIB_PATH . '/hapage.lib.php');

class HA_Router extends HA_Single
{

    /* {{{ 静态变量 */
    /**
     * @lib库路径
     */
    protected static $_baseDir;

    /* }}} */

    /* {{{ public static Object instance() */
    /**
     * 获取单例对象的一个实例
     *
     * @access public static
     * @param  Mixture $mixArg
     * @param  String  $strCls (default null)
     * @return Object
     */
    public static function instance($mixArg = null, $strCls = null)
    {
        return parent::instance(null, is_null($strCls) ? __CLASS__ : $strCls);
    }
    /* }}} */

    /* {{{ public Boolean dispatch() */
    /**
     * 分配请求
     *
     * @access public
     * @return Boolean true or false
     */
    public function dispatch()
    {

        $strCtr = strtolower(trim(HA_Context::instance()->get('controller', 'default')));
        $strCls = sprintf('App_Control_%s', ucfirst($strCtr));

        if (!class_exists($strCls, false)) {
            $file   = sprintf(
                '%s/control/%s.control.php',
                self::$_baseDir, $strCtr
            );

            if (!is_file($file)) {
                $this->_undefined_control($strCtr);
                return false;
            }

            require_once($file);
        }

        $objCtl = new $strCls();
        $mixRet = $objCtl->approot(self::$_baseDir)->excute();
        if (is_object($mixRet) && method_exists($mixRet, 'excute')) {
            $mixRet->excute();
        }

        return true;
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
        if (defined('HA_APP_ROOT')) {
            self::$_baseDir = HA_APP_ROOT;
        } else {
            self::$_baseDir = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
        }

        /**
         * @绑定默认语言包
         */
        HA_Gettext::bind('', self::$_baseDir . '/lang');

        /**
         * @绑定默认配置文件
         */
        HA_Config::bind('', 'ini', self::$_baseDir . '/etc/config.etc.ini');

        $objRes = HA_Config::instance();          /**<  default configure      */
        if ($objRes->get('debug_mode', true) != true) {
            error_reporting(0);
        } else {
            error_reporting(E_ALL ^ E_NOTICE);
        }

        date_default_timezone_set($objRes->get('timezone', 'Asia/Shanghai'));
        HA_Filelog::instance()->cache($objRes->get('log_cache', 0))
            ->level($objRes->get('log_level', HA_Filelog::LOG_LEVEL_ALL & (~HA_Filelog::LOG_LEVEL_DEBUG)))
            ->rotate($objRes->get('log_rotate', HA_Filelog::LOG_ROTATE_DATE))
            ->logfile($objRes->get('log_name', 'access'), $objRes->get('log_path', self::$_baseDir . '/var/logs'));

        $arrOpt = array();
        foreach (array('cookie', 'domain', 'verify', 'create') AS $strKey) {
            $strKey = sprintf('idname.%s', $strKey);
            $arrOpt[$strKey] = $objRes->get($strKey);
        }
        HA_Context::instance($arrOpt);

        return $this;
    }
    /* }}} */

    /* {{{ protected Boolean _undefined_control() */
    /**
     * 控制器未定义
     *
     * @access protected
     * @param  String $control
     * @return Boolean true or false
     */
    protected function _undefined_control($control)
    {
        printf(__('Undefined controller as "%s"'), $control);
        exit;
    }
    /* }}} */

}

function __($strMsg, $domain = '')
{
    $objRes = HA_Context::instance();
    $strLan = is_null($strLan) ? $objRes->get('lang', null) : $strLan;
    $strLan = is_null($strLan) ? $objRes->session('lang', null) : $strLan;
    $strLan = is_null($strLan) ? HA_Config::instance()->get('lang', 'zh_CN') : $strLan;

    return HA_Gettext::instance($strLan)->translate($strMsg, $domain);
}

function _e($strMsg, $domain = '')
{
    echo __($strMsg, $domain);
    return true;
}

