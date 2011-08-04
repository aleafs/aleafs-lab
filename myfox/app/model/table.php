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

    private static $mysql   = null;

    /* }}} */

    /* {{{ 成员变量 */

    public $queries = 0;

    private $option	= null;

    private $route  = null;

    private $column = array();

    private $index  = array();

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
            $info   = self::$mysql->getRow(self::$mysql->query(sprintf(
                "SELECT * FROM %stable_list WHERE tabname = '%s'",
                self::$mysql->option('prefix', ''),
                self::$mysql->escape($this->tbname)
            )));
            $this->option   = (array)$info;
            $this->queries++;
        }
        $key	= strtolower(trim($key));
        return isset($this->option[$key]) ? $this->option[$key] : $default;
    }
    /* }}} */

    /* {{{ public Mixture column() */
    /**
     * 返回表字段
     *
     * @access public
     * @return Mixture
     */
    public function column()
    {
        if (empty($this->column)) {
            $column = self::$mysql->getAll(self::$mysql->query(sprintf(
                "SELECT * FROM %stable_column WHERE tabname='%s' ORDER BY colseqn ASC, autokid ASC",
                self::$mysql->option('prefix'), self::$mysql->escape($this->tbname)
            )));
            $this->queries++;
            $this->column   = array();
            foreach ((array)$column AS $row) {
                $this->column[$row['colname']]  = $row;
            }
        }

        return $this->column;
    }
    /* }}} */

    /* {{{ public Mixture index() */
    /**
     * 返回表索引
     *
     * @access public
     * @return Mixture
     */
    public function index()
    {
        if (empty($this->index)) {
            $index  = self::$mysql->getAll(self::$mysql->query(sprintf(
                "SELECT * FROM %stable_index WHERE tabname='%s' ORDER BY idxseqn ASC, autokid ASC",
                self::$mysql->option('prefix'), self::$mysql->escape($this->tbname)
            )));
            $this->queries++;
            $this->index    = array();
            foreach ((array)$index AS $row) {
                $this->index[$row['idxname']]  = $row;
            }
        }

        return $this->index;
    }
    /* }}} */

    /* {{{ public Mixture route() */
    /**
     * 获取表的路由类型
     *
     * @access public
     * @return Mixture
     */
    public function route()
    {
        if (null === $this->route) {
            $column = preg_split(
                '/[\s,;\/]+/',
                trim($this->get('route_fields'), "{}\t\r\n "),
                -1, PREG_SPLIT_NO_EMPTY
            );

            $this->route    = array();
            foreach ((array)$column AS $item) {
                list($name, $type) = array_pad(explode(':', $item, 2), 2, 'int');
                $this->route[strtolower(trim($name))]  = strtolower(trim($type));
            }
            ksort($this->route);
        }

        return $this->route;
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

        if (empty(self::$mysql)) {
            self::$mysql    = \Myfox\Lib\Mysql::instance('default');
        }
    }
    /* }}} */

}
