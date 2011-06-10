<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: table.php 18 2010-04-13 15:40:37Z zhangxc83 $

namespace Myfox\App\Model;

class Table
{

    /* {{{ 静态变量 */

    private static $objects	= array();

    /* }}} */

    /* {{{ 成员变量 */

    public $queries = 0;

    private $option	= null;

    /* }}} */

    /* {{{ public static Object instance() */
    /**
     * 获取表的实例
     *
     * @access public static
     * @param  String $tbname
     * @return Object
     */
    public static function instance($tbname)
    {
        $tbname	= strtolower(trim($tbname));
        if (empty(self::$objects[$tbname])) {
            self::$objects[$tbname]	= new self($tbname);
        }

        return self::$objects[$tbname];
    }
    /* }}} */

    /* {{{ public static void cleanAllStatic() */
    /**
     * 清理静态数据
     *
     * @access public static
     * @return void
     */
    public static function cleanAllStatic()
    {
        self::$objects  = array();
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 返回属性
     *
     * @access public
     * @return Mixture
     */
    public function get($key, $default = null)
    {
        if (null === $this->option) {
            $mysql  = \Myfox\Lib\Mysql::instance('default');
            $info   = $mysql->getRow($mysql->query(sprintf(
                "SELECT * FROM %stable_list WHERE tabname = '%s'",
                $mysql->option('prefix', ''),
                $mysql->escape($this->tbname)
            )));
            $this->option   = (array)$info;
            $this->queries++;
        }
        $key	= strtolower(trim($key));
        return isset($this->option[$key]) ? $this->option[$key] : $default;
    }
    /* }}} */

    /* {{{ private void __construct() */
    /**
     * 构造函数
     *
     * @access private
     * @return void
     */
    private function __construct($tbname)
    {
        $this->tbname   = strtolower(trim($tbname));
        $this->option   = null;
    }
    /* }}} */

}
