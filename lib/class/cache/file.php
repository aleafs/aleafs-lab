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

use \Aleafs\Lib\Dict;
use \Aleafs\Lib\Exception;

class File
{

    /* {{{ 静态常量 */

    const EXPIRE    = 3600;

    /* }}} */

    /* {{{ 成员变量 */

    private $dict   = null;

    private $time   = null;

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String $prefix
     * @return void
     */
    public function __construct($prefix, $path = null)
    {
        $this->time = time();
        $this->dict = new Dict(sprintf('%s/%s',
            empty($path) ? '/tmp/acache' : rtrim(trim($path), '/'),
            urlencode(trim($prefix, '/'))
        ), 2048);
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
    public function get($key, $tm = false)
    {
        $rs = $this->dict->get($key);
        if (empty($rs)) {
            return null;
        }

        $tm = $tm ? time() : $this->time;
        if (empty($rs['t']) || $rs['t'] < $tm) {
            $this->dict->delete($key);
            return null;
        }

        return isset($rs['d']) ? $rs['d'] : null;
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
        return $this->dict->set($key, array(
            't' => $this->time + ($expire ? $expire : self::EXPIRE),
            'd' => $value,
        ));
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
     * 删除缓存数据
     *
     * @access public
     * @param String $key
     * @return Boolean true or false
     */
    public function delete($key)
    {
        return $this->dict->delete($key);
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
        return $this->dict->truncate();
    }
    /* }}} */

}

