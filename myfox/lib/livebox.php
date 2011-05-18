<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | livebox.php	        											|
// +--------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: livebox.php 2010-04-23  zhangxc83 Exp $

namespace Myfox\Lib;

use \Myfox\Lib\Cache\Apc;

class LiveBox
{

    /* {{{ 成员变量 */

    private $host   = array();      /**<  服务器列表      */

    private $pool   = array();

    private $offs   = array();      /**<  不可用列表      */

    private $sign   = null;         /**<  不可用列表签名      */

    private $last   = null;         /**<  上次返回的服务器      */

    private $live   = 300;          /**<  自动存活检查时间      */

    private $cache  = null;         /**<  缓存服务      */

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
            $this->cache = new Apc($token, $this->live);
        } else {
            $this->cache = null;
        }

        if (!empty($this->cache)) {
            $this->offs = self::filterOffs($this->cache->get('offs'));
        }
        $this->sign = crc32(json_encode($this->offs));
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
        if (!empty($this->cache)) {
            $offs = self::filterOffs(array_merge(
                (array)$this->cache->get('offs'),
                (array)$this->offs
            ));
            ksort($offs);

            if (crc32(json_encode($offs)) != $this->sign) {
                $this->cache->set('offs', $offs, intval(1.2 * $this->live));
            }
        }
    }
    /* }}} */

    /* {{{ public Object register() */
    /**
     * 添加一台服务器
     *
     * @access public
     * @param  Mixture $host
     * @return Object $this
     */
    public function register($host, $id = null)
    {
        $this->host[(null === $id) ? count($this->host) : $id] = $host;
        $this->pool = array();
        return $this;
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
    public function setOffline($id = null)
    {
        $id = (null === $id) ? $this->last : $id;
        if (isset($this->host[$id])) {
            $this->offs[$id] = time() + $this->live;
        }
        $this->pool = array();

        return $this;
    }
    /* }}} */

    /* {{{ public Object cleanAllCache() */
    /**
     * 清理所有的对象属性
     *
     * @access public
     * @return void
     */
    public function cleanAllCache()
    {
        $this->host = array();
        $this->pool = array();
        $this->offs = array();
        $this->sign = crc32(json_encode($this->offs));
        $this->last = null;

        if (!empty($this->cache)) {
            $this->cache->cleanAllCache();
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Mixture fetch() */
    /**
     * 随机获取一台可用服务器
     *
     * @access public
     * @return Mixture
     */
    public function fetch()
    {
        if (empty($this->pool)) {
            $this->pool = array_keys(array_diff_key($this->host, $this->offs));
        }
        if (empty($this->pool)) {
            throw new \Myfox\Lib\Exception('There is no available server');
        }

        $this->last = $this->pool[array_rand($this->pool)];
        return $this->host[$this->last];
    }
    /* }}} */

    /* {{{ private static String  sign() */
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

    /* {{{ private static Mixture filterOffs() */
    /**
     * 根据时间过滤不可用列表
     *
     * @access private static
     * @param  Array $offs
     * @return Array
     */
    private static function filterOffs($offs)
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

}

