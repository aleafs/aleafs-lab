<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | MySQL操作类	    					    							|
// |                                                                        | 
// | Based on php5.3.3, with mysqli & mysqlnd                               |
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>	    							|
// +------------------------------------------------------------------------+
//
// $Id: mysql.php 22 2010-04-15 16:28:45Z zhangxc83 $

namespace Myfox\Lib;

use \Myfox\Lib\Config;
use \Myfox\Lib\LiveBox;

class Mysql
{

    /* {{{ 静态变量 */

    private static $objects	= array();

    private static $alias	= array();

    /* }}} */

    /* {{{ */

    private $master;

    private $slave;

    private $handle;

    private $option = array(
        'timeout'   => 5,
        'charset'   => 'utf8',
        'dbname'    => '',
        'logurl'    => '',
    );

    /* }}} */

    /* {{{ public static Object instance() */
    /**
     * 获取实例
     *
     * @access public static
     * @return object
     */
    public static function instance($name)
    {
        $name	= self::normalize($name);
        if (empty(self::$objects[$name])) {
            if (!isset(self::$alias[$name])) {
                throw new \Myfox\Lib\Exception(sprintf('Undefined mysql instance named as "%s"', $name));
            }
            self::$objects[$name]	= new self(self::$alias[$name]);
        }

        return self::$objects[$name];
    }
    /* }}} */

    /* {{{ public static void register() */
    /**
     * 注册别名
     *
     * @access public static
     * @return void
     */
    public static function register($name, $config = null)
    {
        self::$alias[self::normalize($name)] = $config;
    }
    /* }}} */

    /* {{{ public static void removeAllNames() */
    /**
     * 清理所有对象
     *
     * @access public static
     * @return void
     */
    public static function removeAllNames()
    {
        foreach (self::$objects AS $mysql) {
            $mysql->close();
        }

        self::$objects	= array();
        self::$alias	= array();
    }
    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct($config = null, $name = null)
    {
        if (is_scalar($config) && !empty($config)) {
            try {
                $config = \Myfox\Lib\Config::instance($config);
            } catch (\Exception $e) {
                $config = new \Myfox\Lib\Config($config);
            }
            $config = $config->get('');
        }

        $config = (array)$config;
        foreach ($config AS $key => $val) {
            if (isset($this->option[$key])) {
                $this->option[$key] = $val;
            }
        }

        if (!empty($name)) {
            self::$objects[self::normalize($name)]  = &$this;
        }
    }
    /* }}} */

    /* {{{ public void __destruct() */
    /**
     * 
     * @access public
     * @return void 
     */
    public function __destruct()
    {
        $this->close();
    }
    /* }}} */

    /* {{{ public void close() */
    /**
     * 
     */
    public function close()
    {
    }
    /* }}} */

    /* {{{ public Mixture query() */
    /**
     *
     * @access public
     * @return Mixture
     */
    public function query($query, $value = null, $type = null)
    {
    }
    /* }}} */

    /* {{{ public Mixture async() */
    /**
     * 异步请求
     *
     * @access public
     * @return void
     */
    public function async($query, $value = null, $type = null)
    {
    }
    /* }}} */

    /* {{{ public Mixture poll() */
    /**
     * @access public
     * @return Mixture
     */
    public function poll()
    {
    }
    /* }}} */

    /* {{{ private static string normalize() */
    /**
     * 名字归一化
     *
     * @access private static
     * @return string
     */
    private static function normalize($name)
    {
        return strtolower(preg_replace('/\s+/', '', $name));
    }
    /* }}} */

}
