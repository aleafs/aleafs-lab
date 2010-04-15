<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 文件自动加载控制    					    							|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$

class AutoLoad
{

    /* {{{ 静态变量 */
    /**
     * @路径解析规则
     */
    private static $rules	= array();

    /* }}} */

    /* {{{ public static void init() */
    /**
     * 初始化
     *
     * @access public static
     * @return void
     */
    public static function init()
    {
        spl_autoload_register(array(__CLASS__, 'callback'));
    }
    /* }}} */

    /* {{{ public static void register() */
    /*
     * 注册路径解析规则
     *
     * @access public static
     * @param  String $name
     * @param  String $dir
     * @return void
     */
    public static function register($name, $dir)
    {
        $dir = realpath($dir);
        if (empty($dir) || !is_dir($dir)) {
            return;
        }
        self::$rules[self::normalize($name)] = $dir;
    }
    /* }}} */

    /* {{{ public static void unregister() */
    /**
     * 注销路径解析规则
     *
     * @access public static
     * @param  String $name
     * @return void
     */
    public static function unregister($name)
    {
        $name = self::normalize($name);
        if (isset(self::$rules[$name])) {
            unset(self::$rules[$name]);
        }
    }
    /* }}} */

    /* {{{ public static void callback() */
    /**
     * 自动加载回调函数
     *
     * @access public static
     * @param  String $class
     * @return void
     */
    public static function callback($class)
    {
        $ordina	= $class;
        $class	= str_replace('\\', '/', preg_replace('/[^\w\\\\]/is', '', $class));
        $index	= strrpos($class, '/');

        if (false === $index) {
            throw new \Exception('dd');
        }

        $path = strtolower(substr($class, 0, $index));
        $name = strtolower(substr($class, $index + 1));
        foreach (self::$rules AS $key => $dir) {
            if (0 !== strpos($path, $key)) {
                continue;
            }

            $file = $dir . substr($path, strlen($key)) . '/' . $name . '.php';
            if (is_file($file)) {
                require $file;
            } else {
                throw new \Exception(sprintf('File "%s" Not Found.', $file));
            }

            if (!class_exists($ordina)) {
                throw new \Exception(sprintf('Class "%s" Not Found in "%s".', $ordina, $file));
            }

            return;
        }

        throw new \Exception(sprintf('Class "%s" Not Found.', $ordina));
    }
    /* }}} */

    /* {{{ private static String normalize() */
    /**
     * 规则归一化处理
     *
     * @access private static
     * @param  String $name
     * @return String
     */
    private static function normalize($name)
    {
        $name = preg_replace('/[^\w\\\\]/is', '', $name);
        return strtolower(rtrim(str_replace('\\', '/', $name), '/'));
    }
    /* }}} */

}

