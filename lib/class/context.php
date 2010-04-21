<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 上下文环境																|
// +------------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: aleafs <zhangxc83eng@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: context.lib.php 4 2010-03-09 05:20:36Z zhangxc83 $

namespace Aleafs\Lib;

class Context
{

    /* {{{ 静态变量 */

    /**
     * @用户IP
     */
    private static $userip;

    /**
     * @进程ID
     */
    private static $pid;

    /* }}} */

    /* {{{ public static String userip() */
    /**
     * 获取当前用户IP
     *
     * @access public static 
     * @param  Boolean $bolInt (default false)
     * @return String or Integer
     */
    public static function userip($bolInt = false)
    {
        if (null === self::$userip) {
            $arrKey	= array(
                'HTTP_X_FORWARDED_FOR',
                'HTTP_CLIENT_IP',
                'REMOTE_ADDR',
            );

            self::$userip = '127.0.0.1';
            foreach ($arrKey AS $strKey) {
                $strVal	= trim($_SERVER[trim($strKey)]);
                if (false !== stripos($strVal, 'unknown')) {
                    $strVal	= str_ireplace('unknown', '', $strVal);
                }
                $strVal	= reset(array_filter(explode(',', $strVal)));
                if (!empty($strVal)) {
                    self::$userip = $strVal;
                    break;
                }
            }
        }

        return $bolInt ? sprintf('%u', ip2long(self::$userip)) : $userip;
    }
    /* }}} */

    /* {{{ public static String idname() */
    /**
     * 返回统一的用户ID
     *
     * @access public static
     * @return String
     */
    public static function idname()
    {
    }
    /* }}} */

    /* {{{ public static String token() */
    /**
     * 返回当前访问token
     *
     * @access public static
     * @return String
     */
    public static function token()
    {
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
        if (null === self::$pid) {
            self::$pid = getmypid();
        }

        return self::$pid;
    }
    /* }}} */

}

