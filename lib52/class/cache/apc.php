<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | APC缓存类		        											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: apc.php 102 2010-07-02 03:28:27Z zhangxc83 $

class Aleafs_Lib_Cache_Apc
{

    /* {{{ 静态常量 */

    const EXPIRE_TIME	= 1200;		  /**<  seconds    */
    const COMPRESS_SIZE	= 4096;		  /**<  bytes      */

    const SERIALIZE		= 'serialize';
    const UNSERIALIZE	= 'unserialize';

    /* }}} */

    /* {{{ 静态变量 */

    private static $data    = array();

    /* }}} */

    /* {{{ 成员变量 */

    private $prefix		= '';

    private $compress	= false;

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String  $prefix
     * @param  Boolean $compress (default false)
     * @return void
     */
    public function __construct($prefix, $compress = false)
    {
        $this->prefix	= preg_replace('/[\s:]+/', '', $prefix);
        $this->compress	= $compress && function_exists('gzcompress') ? true : false;
    }
    /* }}} */

    /* {{{ public void __destruct() */
    /**
     * 析构函数
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        foreach (self::$data AS $key => &$val) {
            if (empty($val['write'])) {
                continue;
            }
            if (apc_store($key, $val['value'], $val['time'])) {
                unset($val['write']);
            }
        }
    }
    /* }}} */

    /* {{{ public Boolean set() */
    /**
     * 存入数据
     *
     * @access public
     * @param  String $key
     * @param  Mixture $value
     * @param  Integer $expire (default null)
     */
    public function set($key, $value, $expire = null)
    {
        $expire = is_null($expire) ? self::EXPIRE_TIME : (int)$expire;
        self::$data[$this->name($key)] = array(
            'write' => true,
            'time'  => $expire,
            'value' => $this->pack(array(
                'ttl'   => time() + $expire,
                'val'   => $value,
            )),
        );

        return true;
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 取出数据
     *
     * @access public
     * @param  String $key
     * @return Mixture
     */
    public function get($key, $ttl = null)
    {
        $key    = $this->name($key);
        if (isset(self::$data[$key])) {
            $data = self::$data[$key]['value'];
        } else {
            $data = apc_fetch($this->name($key));
        }
        if (false === $data) {
            return null;
        }

        $ttl  = empty($ttl) ? time() : (int)$ttl;
        $data = $this->unpack($data);
        if ($data['ttl'] < $ttl) {
            $this->delete($key);
            return null;
        }

        return $data['val'];
    }
    /* }}} */

    /* {{{ public Boolean delete() */
    /**
     * 删除缓存数据
     *
     * @access public
     * @param  String $key
     * @return Boolean true or false
     */
    public function delete($key)
    {
        $key    = $this->name($key);
        unset(self::$data[$key]);

        return apc_delete($key);
    }
    /* }}} */

    /* {{{ public static Boolean cleanAllCache() */
    /**
     * 清理所有缓存
     *
     * @access public static
     * @return Boolean true or false
     */
    public static function cleanAllCache()
    {
        self::$data = array();
        return apc_clear_cache('user');
    }
    /* }}} */

    /* {{{ public Mixture shell() */
    /**
     * Cache获取shell接口
     *
     * @access public
     * @param  Mixture $callback
     * @param  String  $key
     * @param  Mixture $expire : default null
     * @return Mixture
     */
    public function shell($callback, $key, $expire = null)
    {
        $data = $this->get($key);
        if (empty($data)) {
            $data = call_user_func($callback, $key);
            if (!empty($data)) {
                $this->set($key, $data, $expire);
            }
        }

        return $data;
    }
    /* }}} */

    /* {{{ private String name() */
    /**
     * 修正数据前缀
     *
     * @access private
     * @param  String $key
     * @return String
     */
    private function name($key)
    {
        return sprintf('%s::%s', $this->prefix, $key);
    }
    /* }}} */

    /* {{{ private String pack() */
    /**
     * 打包数据
     *
     * @access private
     * @param  Mixture $data
     * @return String
     */
    private function pack($data)
    {
        if (!$this->compress) {
            return $data;
        }

        $func = self::SERIALIZE;
        $data = $func($data);
        if (strlen($data) >= self::COMPRESS_SIZE) {
            return 'C' . gzcompress($data);
        }

        return 'N' . $data;
    }
    /* }}} */

    /* {{{ private Mixture unpack() */
    /**
     * 数据解包
     *
     * @access private
     * @param  Mixture $data
     * @return Mixture
     */
    private function unpack($data)
    {
        if (!$this->compress) {
            return $data;
        }

        if ($data[0] === 'C') {
            $data = gzuncompress(substr($data, 1));
        } elseif ($data[0] === 'N') {
            $data = substr($data, 1);
        }
        $func = self::UNSERIALIZE;

        return $func($data);
    }
    /* }}} */

}

