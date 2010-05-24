<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Language.php 语言翻译包											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;

use \Aleafs\Lib\Cache\Apc;
use \Aleafs\Lib\Stream\Mo;

class Language
{

    /* {{{ 静态常量 */

    const CACHE_PREFIX  = '#LANG#';

    const CACHE_EXPIRE  = null;

    /* }}} */

    /* {{{ 静态变量 */

    /**
     * @domain => path
     */
    private static $rules   = array();

    /**
     * @mo读取器
     */
    private static $reader  = array();

    /**
     * @调试状态
     */
    private static $status  = array();

    /**
     * @语言
     */
    private static $lang    = 'zh_cn';

    /**
     * @缓存对象
     */
    private static $cache   = null;

    /**
     * @后注册的优先级高
     */
    private static $sorted  = false;

    /* }}} */

    /* {{{ public static void init() */
    /**
     * 初始化lang属性
     *
     * @access public static
     * @param  String  $lang
     * @param  Boolean $cache : default true
     * @return void
     */
    public static function init($lang, $cache = true)
    {
        $lang = strtolower(trim($lang));
        if ($lang != self::$lang) {
            self::$lang  = $lang;
            self::$cache = null;
        }

        if ($cache && empty(self::$cache) && function_exists('apc_add')) {
            self::$cache = new Apc(self::CACHE_PREFIX . $lang);
        }
    }
    /* }}} */

    /* {{{ public static void register() */
    /**
     * 注册语言包
     *
     * @access public static
     * @param  String $domain
     * @param  String $mofile
     * @return void
     */
    public static function register($domain, $mofile)
    {
        self::$rules[self::normailize($domain)] = realpath($mofile);
        self::$sorted = false;
    }
    /* }}} */

    /* {{{ public static void unregister() */
    /**
     * 注销语言包
     *
     * @access public static
     * @param  String $domain : default null
     * @return void
     */
    public static function unregister($domain = null)
    {
        if (empty($domain)) {
            self::$reader   = array();
            self::$rules    = array();
            return;
        }

        $domain = self::normailize($domain);
        if (isset(self::$reader[$domain])) {
            unset(self::$reader[$domain]);
        }
        if (isset(self::$rules[$domain])) {
            unset(self::$rules[$domain]);
        }
    }
    /* }}} */

    /* {{{ public static String translate() */
    /**
     * 获取字符串的翻译
     *
     * @access public static
     * @param  String $string
     * @param  String $domain : default null
     * @return String
     */
    public static function translate($string, $domain = null)
    {
        if (!empty(self::$cache)) {
            return self::$cache->shell(
                array(__CLASS__, '_gettext'),
                json_encode(array('d' => $domain, 's' => $string)),
                self::CACHE_EXPIRE
            );
        }

        return self::_gettext($string, $domain);
    }
    /* }}} */

    /* {{{ public static String _gettext() */
    /**
     * 从mo文件获取字符串的翻译
     *
     * @access public static
     * @param  String $string
     * @param  String $domain : default null
     * @return String
     */
    public static function _gettext($string, $domain)
    {
        if (null !== $domain) {
            $domain = self::normailize($domain);
            if (!isset(self::$rules[$domain])) {
                return $string;
            }

            $rules  = array(self::$rules[$domain]);
        } else {
            if (!self::$sorted) {
                self::$rules  = array_reverse(self::$rules, true);
                self::$sorted = true;
            }
            $rules  = self::$rules;
        }

        foreach ((array)$rules AS $key => $path) {
            if (empty(self::$reader[$key])) {
                $file = self::mofile($key, $path);
                if (empty($file)) {
                    continue;
                }
                self::$reader[$key] = new Mo($file);
            }

            if (isset(self::$status[$key])) {
                self::$status[$key]['read']++;
            } else {
                self::$status[$key] = array(
                    'read'  => 1,
                );
            }

            if (false !== ($result = self::$reader[$key]->gettext($string))) {
                return $result;
            }
        }

        return $string;
    }
    /* }}} */

    /* {{{ public static Mixture debug() */
    /**
     * 获取调试信息
     *
     * @access public static
     * @param  Mixture $domain : default null
     * @return Mixture
     */
    public static function debug($domain = null)
    {
        if (null !== $domain) {
            $domain = self::normailize($domain);
            if (empty(self::$reader[$domain])) {
                return null;
            }

            return self::$reader[$domain]->debugInfo();
        }

        $debug = array();
        reset(self::$reader);
        foreach (self::$reader AS $domain => $reader) {
            $debug[$domain] = $reader->debugInfo();
        }

        return $debug;
    }
    /* }}} */

    /* {{{ private static String normailize() */
    /**
     * 对domain进行归一化
     *
     * @access private static
     * @param  String $domain
     * @return String
     */
    private static function normailize($domain)
    {
        return empty($domain) ? '' : strtolower(trim($domain));
    }
    /* }}} */

    /* {{{ private static String mofile() */
    /**
     * 根据domain和path获取正确的mofile路径
     *
     * @access private static
     * @param  String $domain
     * @param  String $path
     * @return String
     */
    private static function mofile($domain, $path)
    {
        if (empty($path)) {
            return null;
        }

        if (is_file($path)) {
            return $path;
        }

        if (false === ($list = glob($path . '/*.mo'))) {
            return null;
        }

        $mofile = array();
        foreach ($list AS $file) {
            $base = strtolower(basename($file, '.mo'));
            if (0 === strcmp($base, sprintf('%s.%s', $domain, self::$lang))) {
                return $file;
            }

            $mofile[$base] = $file;
        }

        if (isset($mofile[$domain])) {
            return $mofile[$domain];
        }

        if (isset($mofile[self::$lang])) {
            return $mofile[self::$lang];
        }

        return null;
    }
    /* }}} */

}
