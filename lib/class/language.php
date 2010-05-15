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

use \Aleafs\Lib\Stream\Mo;

class Language
{

    private static $rules   = array();

    private static $reader  = array();

    public static function register($domain, $mofile)
    {
        self::$rules[self::normailize($domain)] = realpath($mofile);
    }

    public static function unregister($domain = null)
    {
        if (empty($domain)) {
            self::$reader   = array();
            self::$rules    = array();
            return;
        }

        $domain = self::normailize($domain);
        if (isset(self::$rules[$domain])) {
            if (isset(self::$reader[$domain])) {
                unset(self::$reader[$domain]);
            }
            unset(self::$rules[$domain]);
        }
    }

    public static function translate($string, $domain = null)
    {
        if (null !== $domain) {
            $domain = self::normailize($domain);
            if (!isset(self::$reader[$domain])) {
                if (empty(self::$rules[$domain])) {
                    return $string;
                }
                self::$reader[$domain] = new Mo(self::$rules[$domain]);
            }

            return self::$reader[$domain]->gettext($string);
        } else {
            $reader = self::$reader;
        }

        foreach ($reader AS $mo) {
            if (false !== ($result = $mo->translate($string))) {
                return $result;
            }
        }

        return $string;
    }

    private static function normailize($domain)
    {
        return empty($domain) ? '' : strtolower(trim($domain));
    }

}
