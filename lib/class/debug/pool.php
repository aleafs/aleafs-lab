<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Debug 工具类														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: var.php 48 2010-04-26 15:58:11Z zhangxc83 $

namespace Aleafs\Lib\Debug;

class Pool
{

    /* {{{ 静态变量 */

    private static $open  = false;

    private static $debug = array();

    /* }}} */

    /* {{{ public static void openDebug() */
    /**
     * 切换debug开关
     *
     * @access public static
     * @param  Boolean $open
     * @return void
     */
    public static function openDebug($open)
    {
        self::$open = (bool)$open;
    }
    /* }}} */

    /* {{{ public static void push() */
    /**
     * 压入debug数据
     *
     * @access public static
     * @param  String $key
     * @param  Mixture $val
     * @return void
     */
    public static function push($key, $val)
    {
        if (!self::$open) {
            return false;
        }

        if (!isset(self::$debug[$key])) {
            self::$debug[$key] = $val;
            return $val;
        }

        if (!is_array(self::$debug[$key])) {
            self::$debug[$key] = array(self::$debug[$key]);
        }
        self::$debug[$key][] = $val;

        return count(self::$debug[$key]);
    }
    /* }}} */

    /* {{{ public static void clean() */
    /**
     * 清理所有debug数据
     *
     * @access public static
     * @return void
     */
    public static function clean() 
    {
        self::$debug = array();
    }
    /* }}} */

    /* {{{ public static String dump() */
    /**
     * 打出debug数据
     *
     * @access public static
     * @param  String $key (default null)
     * @return String
     */
    public static function dump($key)
    {
        if (null === $key) {
            return var_export(self::$debug, true);
        }

        if (!isset(self::$debug[$key])) {
            return 'NULL';
        }

        return var_export(self::$debug[$key], true);
    }
    /* }}} */

}

