<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 上下文环境																|
// +------------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: context.lib.php 4 2010-03-09 05:20:36Z zhangxc83 $

class Aleafs_Lib_Context
{

    /* {{{ 静态变量 */

    /**
     * @数据列表
     */
    private static $data    = array();

    /* }}} */

    /* {{{ public static void register() */
    /**
     * 注册一个变量
     *
     * @access public static
     * @param  String $key
     * @param  Mixture $val
     * @return void
     */
    public static function register($key, $val)
    {
        self::$data[(string)$key] = $val;
    }
    /* }}} */

    /* {{{ public static void unregister() */
    /**
     * 注销一个变量
     *
     * @access public static
     * @param  String $key
     * @return void
     */
    public static function unregister($key)
    {
        $key = (string)$key;
        if (isset(self::$data[$key])) {
            unset(self::$data[$key]);
        }
    }
    /* }}} */

    /* {{{ public static void cleanAllContext() */
    /**
     * 清理所有上下文数据
     *
     * @access public static
     * @return void
     */
    public static function cleanAllContext()
    {
        self::$data = array();
    }
    /* }}} */

    /* {{{ public static Mixture get() */
    /**
     * 获取变量
     *
     * @access public static
     * @param  String $key
     * @param  Mixture $default : default null
     * @return Mixture
     */
    public static function get($key, $default = null)
    {
        $key = (string)$key;
        if (isset(self::$data[$key])) {
            return self::$data[$key];
        }

        return $default;
    }
    /* }}} */

    /* {{{ public static Mixture userip() */
    /**
     * 获取当前用户IP
     *
     * @access public static
     * @param  Boolean $bolInt (default false)
     * @return String or Integer
     */
    public static function userip($bolInt = false)
    {
        if (null === ($ret = self::get('__ip__'))) {
            $ret = self::_userip();
            self::register('__ip__', $ret);
        }

        return $bolInt ? sprintf('%u', ip2long($ret)) : $ret;
    }
    /* }}} */

    /* {{{ public static Integer pid() */
    /**
     * 获取当前进程号
     *
     * @access public static
     * @return Integer
     */
    public static function pid()
    {
        if (null === ($ret = self::get('__pid__'))) {
            $ret = is_callable('posix_getpid') ? posix_getpid() : getmypid();
            self::register('__pid__', $ret);
        }

        return $ret;
    }
    /* }}} */

    /* {{{ public static Mixture uagent() */
    /**
     * 获取用户浏览器
     *
     * @access public static
     * @return String
     */
    public static function uagent()
    {
        if (null === ($ret = self::get('__uagent__'))) {
            self::register('__uagent__', self::_uagent());
        }

        return $ret;
    }
    /* }}} */

    /* {{{ private static String _userip() */
    /**
     * 读取用户实际IP
     *
     * @access private static
     * @return String
     */
    private static function _userip()
    {
        $check  = array(
            'HTTP_VIA',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        );

        foreach ($check AS $key) {
            if (empty($_SERVER[$key])) {
                continue;
            }

            if (!preg_match_all('/\d+\.\d+\.\d+.\d+/', $_SERVER[$key], $match)) {
                continue;
            }

            $match  = reset($match);
            return end($match);
        }

        return 'unknown';
    }
    /* }}} */

    /* {{{ private static String _uagent() */
    /**
     * 计算用户浏览器类型
     *
     * @access private static
     * @return String
     */
    private static function _uagent()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return '';
        }

        $ua = $_SERVER['HTTP_USER_AGENT'];

        return $ua;
    }
    /* }}} */

}

