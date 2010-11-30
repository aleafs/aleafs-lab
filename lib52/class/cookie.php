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
// $Id: cookie.php 96 2010-06-02 15:54:01Z zhangxc83 $

class Aleafs_Lib_Cookie
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
    private static $prop = array(
        'domain' => null,
        'path'   => '/',
        'expire' => 0,
        'secure' => false,
    );

    /* }}} */

    /* {{{ public static void init() */
    /**
     * Cookie初始化
     *
     * @access public static
     * @paran  Mixture $ini (default null)
     * @param  Mixture $data (default null)
     * @return void
     */
    public static function init($ini = null, $data = null)
    {
        foreach ((array)$ini AS $key => $val) {
            $key = strtolower(trim($key));
            if (isset(self::$prop[$key])) {
                self::$prop[$key] = $val;
            }
        }

        self::$data = is_array($data) ? $data : $_COOKIE;
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
            self::$prop['secure'],
            false
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

