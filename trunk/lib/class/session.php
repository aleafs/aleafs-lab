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

class Session
{

    /* {{{ 静态变量 */

    /**
     * @是否已经初始化
     */
    private static $inited = false;

    /**
     * @用于析构的对象
     */
    private static $killer = null;

    /**
     * @相关属性
     */
    private static $prop = array(
        'session.name'  => 'PHPSESSID',
        'cookie.domain' => null,
        'cookie.path'   => '/',
        'cookie.expire' => 0,
    );

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
    private static $name = null;

    /**
     * @Session ID
     */
    private static $ssid = null;

    /* }}} */

    /* {{{ public static void init() */
    /**
     * 开始session
     *
     * @access public static
     * @param  Mixture $ini (default null)
     * @return void
     */
    public static function init($ini = null)
    {
        if (is_array($ini)) {
            foreach ($ini AS $key => $val) {
                if (!isset(self::$prop[$key])) {
                    continue;
                }
                self::$prop[$key] = $val;
            }
        }

        Cookie::init(self::$prop);

        self::$name = self::$prop['session.name'];
        self::$ssid = trim(Cookie::get(self::$name));
        if (empty(self::$ssid) || !self::check($ssid)) {
            self::$sign = crc32('');
            self::$data = array();
            self::$ssid = self::sessid();
            Cookie::set(self::$name, self::$ssid);
        } else {
            $json = '';
            self::$sign = crc32($json);
            self::$data = json_decode($json, true);
        }

        self::$killer   = new SessionKiller();
        self::$inited   = true;
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
        self::checkInit();

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
        self::checkInit();

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
        self::checkInit();
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
        self::checkInit();

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

    /* {{{ private static Boolean checkInit() */
    /**
     * 初始化Session
     *
     * @access private static
     * @return Boolean true or false
     */
    private static function checkInit()
    {
        return (!self::$inited) && self::init();
    }
    /* }}} */

    /* {{{ private static Boolean check() */
    /**
     * 校验sessid 是否合法
     *
     * @access private static
     * @param  String $ssid
     * @return Boolean true or false
     */
    private static function check($ssid)
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

/* {{{ class SessionKiller() */
class SessionKiller
{
    public function __destruct()
    {
        Session::close();
    }
}
/* }}} */

