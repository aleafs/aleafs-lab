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

    const TS    = '__ts__';

    const IP    = '__ip__';

    /* }}} */

    /* {{{ 静态变量 */

    /**
     * @是否已经初始化
     */
    private static $inited  = false;

    /**
     * @用于析构的对象
     */
    private static $killer  = null;

    /**
     * @存储
     */
    private static $store   = null;

    private static $config  = null;

    /**
     * @SESSION数据
     */
    private static $data    = array();

    private static $attr    = array();

    private static $ssid    = null;

    private static $name    = null;

    private static $flush   = false;

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
        self::$config   = Aleafs_Lib_Configer::instance($ini);
        $class  = sprintf('Aleafs_Lib_Session_%s', ucfirst(strtolower(trim(
            self::$config->get('session.handle', 'file')
        ))));

        self::$store    = new $class(self::$config->get('session.namespace', '/tmp'));

        self::$name = self::$config->get('session.name', 'PHPSESSID');
        self::$ssid = trim(Aleafs_Lib_Cookie::get(self::$name));
        if (empty(self::$ssid) || !self::cookieExpire(self::$ssid)) {
            self::$data = array();
            self::$attr = array();
            self::$ssid = self::generate();
            Aleafs_Lib_Cookie::set(self::$name, self::$ssid, self::$config->get('cookie.expire', 0));
        } else {
            $object = json_decode(self::$store->fetch(self::$ssid), true);
            self::$attr = isset($object['attr']) ? (array)$object['attr'] : array();
            if (self::$attr[self::TS] < time() - self::$config->get('session.expire', 1440)) {
                self::$store->delete(self::$ssid);
                self::$data = array();
                self::$attr = array();
            } else {
                self::$data = isset($object['data']) ? (array)$object['data'] : array();
            }
        }

        self::$killer   = new Aleafs_Lib_SessionKiller();
        self::$flush    = false;
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
        $key = trim($key);
        if ($val === self::$data[$key]) {
            return;
        }

        self::$data[$key] = $val;
        self::$flush = true;
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

        self::$attr = array();
        self::$data = array();
        if (self::$store->delete(self::$ssid)) {
            self::$ssid     = null;
            self::$flush    = false;
        }

        Aleafs_Lib_Cookie::set(self::$name, '', -86400);
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

    /* {{{ public static Mixture attr() */
    /**
     * 读取/写入ATTR数据
     *
     * @access public static
     * @return Mixture
     */
    public static function attr($key, $val = null)
    {
        self::checkInit();

        $key = strtolower(trim($key));
        if (null !== $val) {
            self::$attr[$key] = $val;
            self::$flush = true;
        }

        return isset(self::$attr[$key]) ? self::$attr[$key] : null;
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
        $time   = time();
        if ($time - (int)self::attr(self::TS) >= 120 || rand(1, 100) <= 10) {
            self::attr(self::TS, $time);
        }

        if (empty(self::$attr[self::IP])) {
            self::attr(self::IP, Aleafs_Lib_Context::userip(true));
        }

        if (self::$flush && !empty(self::$ssid)) {
            self::$store->set(self::$ssid, self::$data, self::$attr);
        }

        self::$flush = false;
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
        return (!self::$init) && self::init('session');
    }
    /* }}} */

    /* {{{ private static String generate() */
    /**
     * 生成一个新的sessid
     *
     * @access private static
     * @return String
     */
    private static function generate()
    {
        return md5(uniqid(rand(), true));
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

