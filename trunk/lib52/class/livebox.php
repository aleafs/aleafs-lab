<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | livebox.php	        											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: connpool.php 2010-04-23  aleafs Exp $

class Aleafs_Lib_LiveBox
{

    /* {{{ 成员变量 */

    private $host   = array();          /**<  服务器列表      */

    private $pool   = array();          /**<  带权重的选择池  */

    private $offs   = array();          /**<  不可用列表      */

    private $maxInt = 0;                /**<  随即查找时最大随机数 */

    private $sign   = null;       /**<  不可用列表签名      */

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
            $this->cache = new Aleafs_Lib_Cache_Apc($token);
        } else {
            $this->cache = new Aleafs_Lib_Cache_File($token);
        }

        if (!empty($this->cache)) {
            $this->offs = self::filterOffs($this->cache->get('offs'));
        }
        $this->sign = crc32(json_encode($this->offs));

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
        if (!empty($this->cache)) {
            $offs = self::filterOffs(array_merge(
                (array)$this->cache->get('offs'),
                (array)$this->offs
            ));

            if (crc32(json_encode($offs)) != $this->sign) {
                $this->cache->set('offs', $offs, intval(1.2 * $this->live));
            }
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
            $this->pool = null;
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Object cleanAll() */
    /**
     * 清理所有的对象属性
     *
     * @access public
     * @return void
     */
    public function cleanAll()
    {
        $this->host = array();
        $this->offs = array();
        $this->sign = crc32(json_encode($this->offs));
        $this->last = null;

        if (!empty($this->cache)) {
            $this->cache->cleanAllCache();
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Boolean useCache() */
    /**
     * 是否使用缓存
     *
     * @access public
     * @return Boolean true or false
     */
    public function useCache()
    {
        return empty($this->cache) ? false : true;
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
        $this->last = $this->random(array_diff_key($this->host, $this->offs));
        if (null === $this->last) {
            throw new Exception('There is no available server.');
        }

        $server = &$this->host[$this->last];
        $server['times']++;

        return $server['host'];
    }
    /* }}} */

    /* {{{ private Mixture random() */
    /**
     * 根据权重随机选出一台服务器
     *
     * @access private static
     * @param  Array $host
     * @return Mixture
     */
    private function random($host)
    {
        if (empty($this->pool)) {
            $weight = 0;
            $hosts  = array_diff_key($this->host, $this->offs);

            $this->pool = array();
            foreach ($hosts AS $sign => $item) {
                $weight += (int)$item['weight'];
                $this->pool[$sign] = $weight;
            }
            $this->maxInt = $weight;
        }

        if (empty($this->pool)) {
            return null;
        }

        $index  = array_keys($this->pool);
        $random = rand(0, $this->maxInt - 1);
        $sign   = self::search($random, $index, array_values($this->pool));
        if (null !== $sign) {
            return $sign;
        }

        return reset($index);

        $random = rand(0, $weight - 1);
        foreach ($stacks AS $key => $val) {
            if ($val > $random) {
                return $indexs[$key];
            }
        }

        return reset($indexs);
    }
    /* }}} */

    /* {{{ private static String  search() */
    /**
     * 二分法查找对应的服务器
     *
     * @access private
     * @return String
     */
    private static function search($mouse, $index, $value, $left = -1, $right = -1)
    {
        if ($left == -1 || $right == -1) {
            $left   = 0;
            $right  = count($value);
        }

        $middle = (int)(($left + $right) / 2);
        $snoopy = $value[$middle];
        if ((!isset($value[$middle - 1]) || $mouse >= $value[$middle - 1]) && $mouse < $snoopy) {
            return $index[$middle];
        }

        if (abs($right - $left) <= 1) {
            return null;
        }

        if ($mouse < $snoopy) {
            return self::search($mouse, $index, $value, $left, $middle);
        }

        return self::search($mouse, $index, $value, $middle, $right);
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
        ksort($return);

        return $return;
    }
    /* }}} */

}

