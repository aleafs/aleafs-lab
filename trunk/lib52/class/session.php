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

class Aleafs_Lib_Session
{

    /* {{{ 静态常量 */

    /**
     * @标记session过期时间的常量
     */
    const TS    = '__ts__';

    /* }}} */

    /* {{{ 静态变量 */

    /**
     * @是否已经初始化
     */
    private static $init = false;

    /**
     * @用于析构的对象
     */
    private static $kill = null;

    /**
     * @相关属性
     */
    private static $prop = array(
        'session.name'      => 'PHPSESSID',
        'session.expire'    => 1800,
        'touch.delay'       => 180,       /**<  每隔180s强制刷新TS      */
        'touch.ratio'       => 10,        /**<  180s内10%的概率刷新TS      */
        'cookie.domain'     => null,
        'cookie.path'       => '/',
        'cookie.expire'     => 0,
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

    /**
     * @CACHE
     */
    private static $cache;

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
        foreach ((array)$ini AS $key => $val) {
            if (!isset(self::$prop[$key])) {
                continue;
            }
            self::$prop[$key] = $val;
        }

        // TODO: 初始化cache
        self::$cache = new Cache\File('session');

        self::$name = self::$prop['session.name'];
        self::$ssid = trim(Cookie::get(self::$name));
        if (empty(self::$ssid) || !self::cookieExpire($ssid)) {
            self::$data = array();
            self::$ssid = self::sessid();
            Cookie::set(
                self::$name, self::$ssid,
                self::$prop['cookie.domain'],
                self::$prop['cookie.path'],
                self::$prop['cookie.expire'] > 0 ? time() + self::$prop['cookie.expire'] : 0
            );
        } else {
            $json = self::$cache->get(self::$ssid);
            self::$data = json_decode($json, true);
            if (self::sessionExpire()) {
                self::$data = array();
            }
        }

        self::$sign = crc32(json_encode(self::$data));
        self::$kill = new SessionKiller();
        self::$init = true;
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
        self::$cache->delete(self::$ssid);
        Cookie::set(
            self::$name, '',
            self::$prop['cookie.domain'],
            self::$prop['cookie.path'],
            time() - 86400
        );
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
        $time = time();
        if (empty(self::$prop[self::TS]) ||
            $time - self::$data[self::TS] >= self::$prop['touch.delay'] ||
            rand(1, 100) <= self::$prop['touch.ratio'])
        {
            self::$data[self::TS] = $time + self::$prop['session.expire'];
        }

        $sign = crc32(json_encode(self::$data));
        if (self::$sign == $sign) {
            return true;
        }

        self::$cache->set(self::$ssid, self::$data, self::$prop['session.expire']);
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
        return (!self::$init) && self::init();
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

    /* {{{ private static Boolean sessionExpire() */
    /**
     * 检查SESSION是否过期
     *
     * @access private static
     * @return Boolean true or false
     */
    private static function sessionExpire()
    {
        return time() >= self::$data[self::TS] ? true : false;
    }
    /* }}} */

    /* {{{ private static Boolean cookieExpire() */
    /**
     * 检查sessID的cookie是否过期
     *
     * @access private static
     * @param  String  $sid
     * @return Boolean true or false
     */
    private static function cookieExpire($sid)
    {
        return false;
    }
    /* }}} */

}

class Aleafs_Lib_SessionKiller
{
    public function __destruct()
    {
        Aleafs_Lib_Session::close();
    }
}

