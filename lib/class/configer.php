<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 配置操作类															|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;

class Configer
{

    /* {{{ 静态变量 */

    /**
     * @注册的配置信息
     */
    private static $rules = array();

    /**
     * @对象数组
     */
    private static $objs  = array();

    /* }}} */

    /* {{{ 成员变量 */

    /**
     * @读入的数组
     */
    private $option = array();

    /**
     * @是否已经加载
     */
    private $loaded = false;

    /**
     * @数据资源
     */
    private $url;

    /* }}} */

    /* {{{ public static Object instance() */
    /**
     * 获取配置单例
     *
     * @access public static
     * @param  String $key
     * @return Object
     */
    public static function &instance($key)
    {
        $key = strtolower(trim($key));
        if (!isset(self::$objs[$key])) {
            if (!isset(self::$rules[$key])) {
                throw new Exception(sprintf('Undefined config object named as "%s"', $key));
            }
            self::$objs[$key] = new self(self::$rules[$key]);
        }

        return self::$objs[$key];
    }
    /* }}} */

    /* {{{ public static void register() */
    /**
     * 注册一个配置资源
     *
     * @access public static
     * @param  String $key
     * @param  String $url
     * @return void
     */
    public static function register($key, $url)
    {
        $key = strtolower(trim($key));
        $url = trim($url);

        if (isset(self::$rules[$key]) && self::$rules[$key] == $url) {
            return;
        }

        self::$rules[$key] = $url;
        if (isset(self::$objs[$key])) {
            unset(self::$objs[$key]);
        }
    }
    /* }}} */

    /* {{{ public static void unregister() */
    /**
     * 注销已经注册的配置资源
     *
     * @access public static
     * @param  String $key
     * @return void
     */
    public static function unregister($key)
    {
        $key = strtolower(trim($key));
        if (isset(self::$rules[$key])) {
            unset(self::$rules[$key]);
        }
        if (isset(self::$objs[$key])) {
            unset(self::$objs[$key]);
        }
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 获取配置参数
     *
     * @access public
     * @param  String $key
     * @param  Mixture $default (default null)
     * @return Mixture
     */
    public function get($key, $default = null)
    {
        if (!$this->loaded) {
            if (false === ($this->option = self::loadUrl($this->url))) {
                return null;
            }
            $this->loaded = true;
        }
        $key = strtolower(trim($key));

        return isset($this->option[$key]) ? $this->option[$key] : $default;
    }
    /* }}} */

    /* {{{ public static void makeSureRemoveAll() */
    /**
     * 清理所有注册的资源
     *
     * @access public static
     * @return void
     */
    public static function makeSureRemoveAll()
    {
        self::$rules = array() && self::$objs = array();
    }
    /* }}} */

    /* {{{ private void __construct() */
    /**
     * 构造函数
     *
     * @access private
     * @param  String $url
     * @return void
     */
    private function __construct($url)
    {
        $this->url = $url;
    }
    /* }}} */

    /* {{{ private static Mixture loadUrl() */
    /**
     * 解析配置文件
     *
     * @access private static
     * @param  String $url
     * @return Mixture
     */
    private static function loadUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        $url = parse_url($url);
        $cls = empty($url['scheme']) ? array_pop(explode('.', trim($url['path'], "\x00..\x20."))) : $url['scheme'];
        $cls = sprintf('%s\%s', __CLASS__, ucfirst(strtolower($cls)));
        $obj = new $cls($url);

        return $obj->parse();
    }
    /* }}} */

}

