<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 缓存管理类		    					    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>	    							|
// +------------------------------------------------------------------------+
//
// $Id: cache.php 22 2010-04-15 16:28:45Z zhangxc83 $

namespace Myfox\Lib;

class Cache
{

    /* {{{ 静态常量 */

    const MCACHE    = 1;

    const APC       = 2;

    const MEMORY    = 4;

    const EXPIRE    = 86400;

    /* }}} */

    /* {{{ 静态变量 */

    private static $objects = array();

    /* }}} */

    /* {{{ 成员变量 */

    private $prefix;            /**<    缓存前缀 */

    private $mcache;            /**<    存储对象 */

    /* }}} */

    /* {{{ public static Object instance() */
    /**
     * 获取对象
     * 
     * @access public
     * @return Object
     */
    public static function instance()
    {
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 获取值
     *
     * @access public
     * @return Mixture
     */
    public function get($key)
    {
    }
    /* }}} */

    /* {{{ public Boolean add() */
    /**
     * 添加值
     *
     * @access public
     * @return Boolean true or false
     */
    public function add($key, $val, $ttl = self::EXPIRE)
    {
    }
    /* }}} */

    public function set($key, $val, $ttl = self::EXPIRE)
    {
    }

    public function delete($key)
    {
    }

    private function __construct()
    {
    }

}
