<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 业务缓存封装类        												|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: cache.php 2010-05-29 aleafs Exp $

namespace Aleafs\Lib;

class Cache
{

    /* {{{ 静态常量 */

    const EXPIRE    = 86400;

    const MCACHE	= 1;
    const APC		= 2;
    const FILE		= 4;

    /* }}} */

    /* {{{ 静态变量 */

    private static $objects	= array();

    private static $timenow = null;

    /* }}} */

    /* {{{ 成员变量 */

    private $handle;

    private $dversion   = null;

    /* }}} */

    /* {{{ public static Object instance() */
    /**
     * 获取缓存实例
     *
     * @access public static
     * @return Mixture object
     */
    public static function instance($name, $type = self::APC)
    {
        $type   = (int)$type;
        foreach (array(self::MCACHE, self::APC, self::FILE) AS $id) {
            if ($type & $id) {
                $type   = $id;
                break;
            }
        }

        $id = sprintf('%d:%s', $type, trim($name));
        if (empty(self::$objects[$id])) {
            self::$objects[$id] = new self($type, $name);
        }

        if (empty(self::$timenow)) {
            self::$timenow  = time();
        }

        return self::$objects[$id];
    }
    /* }}} */

    /* {{{ public Boolean dversion() */
    /**
     * 设置数据版本号
     *
     * @access public
     * @return Boolean true
     */
    public function dversion($dv)
    {
        $this->dversion = $dv;
        return true;
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 获取CACHE数据
     *
     * @access public
     * @return Mixture
     */
    public function get($key, $tm = false)
    {
        return $this->unpack($this->handle->get($key, $tm));
    }
    /* }}} */

    /* {{{ public Boolean set() */
    /**
     * 添加/更新数据
     *
     * @access public
     * @return Boolean true or false
     */
    public function set($key, $value, $expire = null)
    {
        return $this->handle->set($key, $this->pack(
            $value, null === $expire ? self::EXPIRE : (int)$expire
        ), 86400 << 5 - 86400 << 1);
    }
    /* }}} */

    /* {{{ public Boolean add() */
    /**
     * 添加数据
     *
     * @access public
     * @return Boolean true or false
     */
    public function add($key, $value, $expire = null)
    {
        if (null !== $this->get($key)) {
            return false;
        }

        return $this->set($key, $value, $expire);
    }
    /* }}} */

    /* {{{ public Boolean delete() */
    /**
     * 删除缓存
     *
     * @access public
     * @return Boolean true or false
     */
    public function delete($key)
    {
        return $this->handle->delete($key);
    }
    /* }}} */

    /* {{{ private void __construct() */
    /**
     * 构造函数
     *
     * @access private
     * @return void
     */
    private function __construct($type, $name)
    {
        switch ($type) {
        case self::MCACHE:
            $this->handle   = new Cache\Mcache($name);
            break;

        case self::APC:
            $this->handle   = new Cache\Apc($name);
            break;

        default:
            $this->handle   = new Cache\File($name);
            break;
        }
    }
    /* }}} */

    /* {{{ private Mixture pack() */
    /**
     * 数据打包
     *
     * @access private
     * @return Mixture
     */
    private function pack($data, $expire)
    {
        return array(
            'v' => $this->dversion,
            'e' => $expire > 0 ? 0 : self::$timenow + $expire,
            'd' => $data,
        );
    }
    /* }}} */

    /* {{{ private Mixture unpack() */
    /**
     * 数据解包
     *
     * @access private
     * @return Mixture
     */
    private function unpack($data)
    {
        if (!isset($data['v']) || !isset($data['e']) || !isset($data['d'])) {
            return null;
        }

        if ($data['e'] > 0 && $data['e'] < self::$timenow) {
            return null;
        }

        if (!empty($this->dversion) && $this->dversion != $data['v']) {
            return null;
        }

        return $data['d'];
    }
    /* }}} */

}

