<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | connpool.php	        											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: connpool.php 2010-04-23  aleafs Exp $

namespace Aleafs\Lib;

class ConnPool
{

    /* {{{ 成员变量 */

    private $host   = array();          /**<  服务器列表      */

    private $offs   = array();          /**<  不可用列表      */

    private $last   = null;       /**<  上次返回的服务器      */

    private $live   = 300;        /**<  自动存活检查时间      */

    private $cache  = null;       /**<  缓存服务      */

    /* }}} */

    /* {{{ public Object __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String  $token : 缓存前缀
     * @param  Integer $live  : default 300
     * @return Object $this
     */
    public function __construct($token, $live = 300)
    {
        $this->live  = max(0, (int)$live);

        if (function_exists('apc_add')) {
            $this->cache = new Cache\Apc($token);
        } else {
            $this->cache = null;
        }

        if (!empty($this->cache)) {
            $this->offs = $this->filterOffs($this->cache->get('offs'));
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Boolean __destruct() */
    /**
     * 析构函数
     *
     * @access public
     * @return Boolean true
     */
    public function __destruct()
    {
        if (!empty($this->cache) && !empty($this->offs)) {
            $this->cache->set(
                'offs',
                $this->filterOffs(array_merge(
                    (array)$this->cache->get('offs'),
                    $this->offs
                )),
                intval(1.2 * $this->live)
            );
        }

        return true;
    }
    /* }}} */

    /* {{{ public Object register() */
    /**
     * 添加一台服务器
     *
     * @access public
     * @param  Mixture $host
     * @param  Integer $weight (default 1)
     * @return Object $this
     */
    public function register($host, $weight = 1)
    {
        $this->host[self::sign($host)] = array(
            'host'  => $host,
            'weight'=> max(1, (int)$weight),
            'times' => 0,
        );

        return $this;
    }
    /* }}} */

    /* {{{ public Object unregister() */
    /**
     * 注销一台服务器
     *
     * @access public
     * @param  Mixture $host
     * @return Object $this
     */
    public function unregister($host)
    {
        $sign = self::sign($host);
        if (isset($this->host[$sign])) {
            unset($this->host[$sign]);
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Mixture getHost() */
    /**
     * 随机获取一台可用服务器
     *
     * @access public
     * @return Mixture
     */
    public function getHost()
    {
        $this->last = self::random(array_diff_key($this->host, $this->offs));
        if (null === $this->last) {
            throw new Exception('There is no available server.');
        }

        $server = &$this->host[$this->last];
        $server['times']++;

        return $server['host'];
    }
    /* }}} */

    /* {{{ public Object setOffline() */
    /**
     * 标记一台服务器为不可用
     *
     * @access public
     * @param  Mixture $host (default null)
     * @return Object $this
     */
    public function setOffline($host = null)
    {
        $sign = (null === $host) ? $this->last : self::sign($host);
        if (isset($this->host[$sign])) {
            $this->offs[$sign] = time() + $this->live;
        }

        return $this;
    }
    /* }}} */

    /* {{{ private Mixture filterOffs() */
    /**
     * 根据时间过滤不可用列表
     *
     * @access private
     * @param  Array $offs
     * @return Array
     */
    private function filterOffs($offs)
    {
        $tsamp  = time();
        $return = array();
        foreach ((array)$offs AS $host => $time) {
            if ($time <= $tsamp) {
                continue;
            }
            $return[$host] = $time;
        }

        return $return;
    }
    /* }}} */

    /* {{{ private static String sign() */
    /**
     * 服务器签名
     *
     * @access private static
     * @param  Mixture $host
     * @return String
     */
    private static function sign($host)
    {
        if (is_scalar($host)) {
            return strtolower(trim($host));
        }

        return json_encode($host);
    }
    /* }}} */

    /* {{{ private static Mixture random() */
    /**
     * 根据权重随机选出一台服务器
     *
     * @access private static
     * @param  Array $host
     * @return Mixture
     */
    private static function random($host)
    {
        if (empty($host)) {
            return null;
        }

        $weight = 0;
        $indexs = array();
        $stacks = array();
        foreach ((array)$host AS $sign => $item) {
            $weight += (int)$item['weight'];
            $indexs[] = $sign;
            $stacks[] = $weight;
        }

        $random = rand(0, $weight - 1);
        foreach ($stacks AS $key => $val) {
            if ($val > $random) {
                return $indexs[$key];
            }
        }

        return $indexs[0];
    }
    /* }}} */

}

