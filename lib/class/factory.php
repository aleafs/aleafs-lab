<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Factory Class 														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: log.php 33 2010-04-21 15:41:56Z zhangxc83 $

namespace Aleafs\Lib;

class Factory
{

    /* {{{ 静态变量 */

    private static $alias   = array();        /**<  别名      */

    private static $reg	= array();        /**<  属性池      */

    private static $obj	= array();        /**<  对象池      */

    private static $log = array();

    /* }}} */

    public static function alias($class, $name, $arg = null)
    {
        $arg = func_get_args();
        self::$alias[self::objIndex($class, $name)] = array_slice($arg, 2);
    }

    /* {{{ public static void register()
     * @access public static
     * @param  String $class
     * @param  String $name
     * @return void
     */
    public static function register($class, $name, $arg = null)
    {
        $arg = func_get_args();
        self::$reg[self::objIndex($class, $name)] = array_slice($arg, 2);
    }
    /* }}} */

    /* {{{ public static void unregister() */
    /**
     * 注销一个已经注册的对象
     *
     * @access public static
     * @access public static
     * @param  String $class
     * @param  String $name
     * @return void
     */
    public static function unregister($class, $name)
    {
        $index	= self::objIndex($class, $name);
        if (isset(self::$reg[$index])) {
            if (isset(self::$obj[$index])) {
                unset(self::$obj[$index]);
            }
            unset(self::$reg[$index]);
        }
    }
    /* }}} */

    /* {{{ public static void removeAllObject() */
    /**
     * 清理掉所有对象和注册信息
     *
     * @access public static
     * @param  Boolean $reg (default false)
     * @return void
     */
    public static function removeAllObject($reg = false)
    {
        self::$obj	= array();
        if ($reg) {
            self::$reg	= array();
        }
    }
    /* }}} */

    /* {{{ public static Object getObject() */
    /**
     * 获取一个类的指定实例
     *
     * @access public static
     * @param  String $class
     * @param  String $name
     * @return Object (refferrence)
     */
    public static function &getObject($class, $name)
    {
        $index	= self::objIndex($class, $name);
        if (empty(self::$obj[$index])) {
            if (empty(self::$reg[$index])) {
                throw new Exception(sprintf(
                    'Unregistered object name as "%s" for class "%s"',
                    $name, $class
                ));
            }
            if (!class_exists($class)) {
                AutoLoad::callback($class);
            }
            $ref = new \ReflectionClass($class);
            self::$obj[$index] = $ref->newInstanceArgs(self::$reg[$index]);
        }

        return self::$obj[$index];
    }
    /* }}} */

    /* {{{ public static Object getLog() */
    /**
     * 获取日志对象
     *
     * @access public static
     * @param  String $url
     * @return Object
     */
    public static function &getLog($url)
    {
        if (empty(self::$log[$url])) {
            self::$log[$url] = new Log($url);
        }

        return self::$log[$url];
    }
    /* }}} */

    /* {{{ private static String objIndex() */
    /**
     * 构造对象索引
     *
     * @access private static
     * @param  String $class
     * @param  String $name
     * @return String
     */
    private static function objIndex($class, $name)
    {
        return sprintf('%s\%s', self::normalize($class), self::normalize($name));
    }
    /* }}} */

    /* {{{ private static String normalize() */
    /**
     * 类名归一化处理
     *
     * @access private static
     * @param  String $class
     * @return String
     */
    private static function normalize($class)
    {
        $class = preg_replace('/\s+/', '', preg_replace('/[\/\\\]+/', '/', $class));
        return strtolower(trim($class, '/'));
    }
    /* }}} */

}

