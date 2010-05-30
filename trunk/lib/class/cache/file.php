<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 文件缓存类         												|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: file.php 2010-05-29 aleafs Exp $

namespace Aleafs\Lib\Cache;

use \Aleafs\Lib\Exception;

class File
{

    /* {{{ 静态常量 */

    const EXPIRE    = 3600;

    /* }}} */

    /* {{{ 成员变量 */

    private $path   = '/tmp/acache';

    private $mode   = 0744;

    private $prefix = '';

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String $prefix
     * @return void
     */
    public function __construct(String $prefix, $path = null, $mode = 0744)
    {
        $this->prefix   = (string)$prefix;
        $this->mode     = $mode;
        if (!empty($path)) {
            $this->path = trim((string)$path);
        } else {
            $this->path = '/tmp/acache';
        }
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 读取CACHE内容
     *
     * @access public
     * @param  String $key
     * @return Mixture
     */
    public function get($key)
    {
        $res = $this->getfile($key, false);
        if (!is_file($res)) {
            return null;
        }

        $ret = json_decode(file_get_contents($res), true);
        if (empty($ret) || empty($ret['ttl']) || $ret['ttl'] < time()) {
            unlink($res);
            return null;
        }

        return $ret['val'];
    }
    /* }}} */

    /* {{{ public Boolean set() */
    /**
     * 存储CACHE数据
     *
     * @access public
     * @param  String $key
     * @param  Mixture $value
     * @param  Interger $expire : default null
     * @return Boolean true or false
     */
    public function set($key, $value, $expire = null)
    {
        $now = time();
        return file_put_contents(
            $this->getfile($key, true),
            json_encode(array(
                'now'   => $now,
                'ttl'   => $now + ($expire ? $expire : self::EXPIRE),
                'val'   => $value,
            )),
            LOCK_EX, null
        ) ? true : false;
    }
    /* }}} */

    /* {{{ public Boolean delete() */
    /**
     * 删除缓存数据
     *
     * @access public
     * @param String $key
     * @return Boolean true or false
     */
    public function delete($key)
    {
        $res = $this->getfile($key, false);
        if (is_file($res) && !unlink($res)) {
            return false;
        }

        return true;
    }
    /* }}} */

    /* {{{ public Mixture shell() */
    /**
     * 缓存回调shell
     *
     * @access public
     * @param  Callback $callback
     * @param  String $key
     * @param  Interger $expire : default null
     * @return Mixture
     */
    public function shell($callback, $key, $expire = null)
    {
        $ret = $this->get($key);
        if (empty($ret)) {
            $ret = call_user_func($callback, $key);
            if ($ret) {
                $this->set($key, $ret, $expire);
            }
        }

        return $ret;
    }
    /* }}} */

    /* {{{ public Boolean cleanAllCache() */
    /**
     * 清理所有缓存
     *
     * @access public
     * @return Boolean true or false
     */
    public function cleanAllCache()
    {
        return self::rmdir(sprintf(
            '%s/%s',
            rtrim($this->path, '/'),
            trim($this->prefix, '/')
        ));
    }
    /* }}} */

    /* {{{ private String getfile() */
    /**
     * 根据KEY获取存储的完整文件名
     *
     * @access private
     * @param  String  $key
     * @param  Boolean $create : default false
     * @return String
     */
    private function getfile($key, $create = false)
    {
        $ret = sprintf(
            '%s/%s/%s',
            rtrim($this->path, '/'),
            trim($this->prefix, '/'),
            trim($this->hash($key), '/')
        );

        if ($create && !is_file($ret)) {
            $dir = dirname($ret);
            if (!is_dir($dir) && !mkdir($dir, $this->mode, true)) {
                throw new Exception('Derectory "%s" not exists, and created failed.');
            }
        }

        return $ret;
    }
    /* }}} */

    /* {{{ private static String hash() */
    /**
     * 根据KEY计算存储的文件名
     *
     * @access private static
     * @param  String $key
     * @return String
     */
    private static function hash($key)
    {
        return implode('/', array_chunk(bin2hex($key), 3));
    }
    /* }}} */

    /* {{{ private static Boolean rmdir() */
    /**
     * 完全清理一个目录
     *
     * @access private static
     * @param  String $dir
     * @return Boolean true or false
     */
    private static function rmdir($dir)
    {
        if (!is_dir($dir)) {
            return true;
        }

        if (false === ($ret = glob($dir . '/*'))) {
            return false;
        }

        foreach ($ret AS $sub) {
            if (is_dir($sub)) {
                if (!self::rmdir($sub)) {
                    return false;
                }
            } else {
                if (!unlink($sub)) {
                    return false;
                }
            }
        }

        return true;
    }
    /* }}} */

}

