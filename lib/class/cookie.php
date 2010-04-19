<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Cookie操作类														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;

class Cookie
{

    /* {{{ 静态变量 */

    /**
     * @Cookie数据
     */
    private static $data = array();

    /**
     * @初始化标记
     */
    private static $init = false;

    /**
     * @Cookie属性
     */
    private static $prop = array();

    /* }}} */

    /* {{{ public static void init() */
    /**
     * Cookie初始化
     *
     * @access public static
     * @paran  Mixture $ini (default null)
     * @param  Mixture $val (default null)
     * @return void
     */
    public static function init($ini = null, $val = null)
    {
        if (is_array($ini)) {
            self::$prop = array();
            foreach (array('domain', 'path', 'expire') AS $key) {
                self::$prop[$key] = isset($ini[$key]) ? $ini[$key] : '';
            }
        }

        self::$data = is_array($val) ? $val : $_COOKIE;
    }
    /* }}} */

    /* {{{ public static void set() */
    /**
     * 设置cookie值
     *
     * @access public static
     * @param  String $key
     * @param  Mixture $val
     * @return void
     */
    public static function set($key, $val)
    {
        self::checkInit();

        $key = trim($key);
        if (empty($val)) {
            unset(self::$data[$key]);
        } else {
            self::$data[$key] = $val;
        }
        return setcookie(
            $key, $val,
            self::$prop['expire'] > 0 ? time() + self::$prop['expire'] : 0,
            self::$prop['path'],
            self::$prop['domain'],
            false, false
        );
    }
    /* }}} */

    /* {{{ public static Mixture get() */
    /**
     * 获取Cookie值
     *
     * @access public static
     * @return Mixture
     */
    public static function get($key)
    {
        self::checkInit();

        $key = trim($key);
        if (!isset(self::$data[$key])) {
            return null;
        }

        return self::$data[$key];
    }
    /* }}} */

    /* {{{ private static void checkInit() */
    /**
     * 检查是否初始化
     *
     * @access private static
     * @return void 
     */
    private static function checkInit()
    {
        if (self::$init) {
            return;
        }

        self::init();
        self::$init = true;
    }
    /* }}} */

}

