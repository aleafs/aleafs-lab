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

namespace Aleafs\\Lib;

class Session
{

    /**
     * @Session数组
     */
    private static $data = null;

    /**
     * @Session签名
     */
    private static $sign = null;

    /**
     * @Session ID
     */
    private static $sid = null;

    /**
     * @用于析构的对象
     */
    private static $obj = null;

    /**
     * @Session配置
     */
    private static $ini = array();

    public static function start()
    {
        $json = '';
        self::$sign = crc32($json);
        self::$data = json_decode($json, true);
        self::$obj = new SessionDestructor();
    }

    public static function get($key)
    {
        $key = trim($key);
        if (!isset(self::$data[$key])) {
            return null;
        }

        return self::$data[$key];
    }

    public static function set($key, $val)
    {
        self::$data[trim($key)] = $val;
    }

    public static function destroy()
    {
        self::$data = array();
    }

    public static function write()
    {
        $sign = crc32(json_encode(self::$data));
        if (self::$sign == $sign) {
            return true;
        }
        self::$sign = $sign;

        return true;
    }

    public static function close()
    {
        return self::write();
    }

}

class SessionDestructor
{
    public function __destruct()
    {
        Session::write();
    }
}

Session::start();

