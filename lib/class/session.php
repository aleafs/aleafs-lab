<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Session操作类														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: session.php 2010-04-19 13:54:32 pengchun Exp $

namespace Aleafs\Lib;

use \Aleafs\Lib\Cookie;

class Session
{

    /* {{{ 静态变量 */

    /**
     * @是否已经初始化
     */
    private static $started = false;

    /**
     * @用于析构的对象
     */
    private static $destruct = null;

    /**
     * @Session数组
     */
    private static $data = null;

    /**
     * @Session签名
     */
    private static $sign = null;

    /**
     * @Cookie名字
     */
    private static $name = 'PHPSESSID';

    /**
     * @Session ID
     */
    private static $sid = null;

    /* }}} */

    /* {{{ public static void start() */
    /**
     * 开始session
     *
     * @access public static
     * @param  Mixture $ini (default null)
     * @return void
     */
    public static function start($ini = null)
    {
        Cookie::init(array());

        self::$sid = trim(Cookie::get(self::$name));
        if (empty(self::$sid) || !self::check($sid)) {
            self::$sid  = self::sessid();
            Cookie::set(self::$name, self::$sid);

            self::$sign = crc32('');
            self::$data = array();
        } else {
            $json = '';
            self::$sign = crc32($json);
            self::$data = json_decode($json, true);
        }

        self::$destruct = new SessionDestructor();
        self::$started  = true;
    }
    /* }}} */

    /* {{{ public static void set() */
    /**
     * 写入session
     *
     * @access public static
     * @param  String $key
     * @param  Mixture $val
     * @param  Boolean $flush (default false)
     * @return void
     */
    public static function set($key, $val, $flush = false)
    {
        self::_init();

        self::$data[trim($key)] = $val;
        if ($flush) {
            self::flush();
        }
    }
    /* }}} */

    /* {{{ public static void destroy() */
    /**
     * 清空session
     *
     * @access public static
     * @return void
     */
    public static function destroy()
    {
        self::_init();

        self::$data = array();
        Cookie::set(self::$name, '');
    }
    /* }}} */

    /* {{{ public static void close() */
    /**
     * 关闭session
     *
     * @access public static
     * @return void
     */
    public static function close()
    {
        self::_init();

        return self::flush();
    }
    /* }}} */

    /* {{{ public static Mixture get() */
    /**
     * 读session
     *
     * @access public static
     * @param  String $key
     * @return Mixture
     */
    public static function get($key)
    {
        self::_init();

        $key = trim($key);
        if (!isset(self::$data[$key])) {
            return null;
        }

        return self::$data[$key];
    }
    /* }}} */

    /* {{{ private static Boolean flush() */
    /**
     * 写入session
     *
     * @access private static
     * @return Boolean true or false
     */
    private static function flush()
    {
        $sign = crc32(json_encode(self::$data));
        if (self::$sign == $sign) {
            return true;
        }

        //TODO: write to storage
        self::$sign = $sign;

        return true;
    }
    /* }}} */

    /* {{{ private static Boolean _init() */
    /**
     * 初始化Session
     *
     * @access private static
     * @return Boolean true or false
     */
    private static function _init()
    {
        return (!self::$started) && self::start();
    }
    /* }}} */

    /* {{{ private static Boolean check() */
    /**
     * 校验sessid 是否合法
     *
     * @access private static
     * @param  String $sid
     * @return Boolean true or false
     */
    private static function check($sid)
    {
        return true;
    }
    /* }}} */

    /* {{{ private static String sessid() */
    /**
     * 生成一个新的sessid
     *
     * @access private static
     * @return String
     */
    private static function sessid()
    {
    }
    /* }}} */

}

/* {{{ class SessionDestructor() */
class SessionDestructor
{
    public function __destruct()
    {
        Session::close();
    }
}
/* }}} */

