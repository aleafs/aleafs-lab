<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: router.php 18 2010-04-13 15:40:37Z zhangxc83 $

namespace Myfox\App\Model;

use \Myfox\Lib\Mysql;
use \Myfox\App\Model\Table;

class Router
{

    /* {{{ 静态常量 */

    const FLAG_PRE_IMPORT	= 1;	/**<	路由计算完，等待装载数据	*/
    const FLAG_IMPORT_END	= 2;	/**<	数据装完，等待路由生效		*/
    const FLAG_NORMAL_USE	= 3;	/**<	数据装完，路由生效			*/
    const FLAG_PRE_RESHIP	= 4;	/**<	等待重装					*/
    const FLAG_IS_LOCKING	= 5;	/**<	热数据迁移时使用			*/
    const FLAG_IS_DELETED	= 0;	/**<	废弃路由，等待删除			*/

    const ROUTE_TYPE_INT    = 0;
    const ROUTE_TYPE_DATE   = 1;

    const MIRROR    = 0;            /**<    镜像表 */
    const SHARDING  = 1;            /**<    分区   */

    /* }}} */

    /* {{{ 静态变量 */

    private static $db;

    private static $inited	= false;

    /* }}} */

    /* {{{ public static void init() */
    /**
     * 类初始化
     *
     * @access public static
     * @param  Object $db
     * @return void
     */
    public static function init($db = null)
    {
        if ($db instanceof \Myfox\Lib\Mysql) {
            self::$db	= $db;
        } else {
            self::$db	= \Myfox\Lib\Mysql::instance('default');
        }

        self::$inited	= true;
    }
    /* }}} */

    /* {{{ public static Mixture get() */
    /**
     * 获取路由值
     *
     * @access public static
     * @param  String $tbname
     * @param  Mixture $field
     * @return Mixture
     */
    public static function get($tbname, $field = array())
    {
        $tbname = trim($tbname);
        return self::parse(self::load(
            $tbname, self::filter($tbname, (array)$field)
        ));
    }
    /* }}} */

    /* {{{ public static Boolean set() */
    /**
     * 计算路由
     *
     * @access public static
     * @param  String $tbname
     * @param  Mixture $field
     * @param  Integer $rownum
     * @return Mixture
     */
    public static function set($tbname, $field, $rownum)
    {
        $routes = self::filter($tbname, (array)$field);
        $table  = Table::instance($tbname);

        $chunk  = (int)$table->get('split_threshold');
        $drift  = $table->get('split_drift');

        $bucket = array();
        if ($chunk > 0 && self::SHARDING == $table->get('route_type')) {
            $lf = (int)$chunk * (1 + $drift);
            while ($rownum > $lf) {
                $rownum -= $chunk;
                $bucket[]   = array(
                    'rows'  => $chunk,
                );
            }
        }

        if ($rownum > 0) {
            $bucket[]   = array(
                'rows'  => $rownum,
            );
        }

        foreach ($bucket AS $item) {
        }

        // xxx: write to db

        return $bucket;
    }
    /* }}} */

    /* {{{ public static Integer sign() */
    /**
     * 返回字符串的签名
     *
     * @access public static
     * @param  String $char
     * @return Integer
     */
    public static function sign($char)
    {
        $sign   = 5381;
        for ($i = 0, $len = strlen($char); $i < $len; $i++) {
            $sign   = ($sign << 5) + $sign + ord(substr($char, $i, 1));
        }

        return sprintf('%u', $sign);
    }
    /* }}} */

    /* {{{ private static String filter() */
    /**
     * 过滤路由字段
     *
     * @access private static
     * @return String
     */
    private static function filter($tbname, $field = array())
    {
        $rt = array();
        foreach (Table::instance($tbname)->get('route_fields') AS $val) {
            list($column, $type) = array_values($val);
            if (!isset($field[$column])) {
                throw new \Myfox\Lib\Exception('Column "%s" required for table "%s"', $column, $tbname);
            }

            if (self::ROUTE_TYPE_DATE == $type) {
                $rt[$column]    = date('Ymd', strtotime($field[$column]));
            } else {
                $rt[$column]    = 0 + $field[$column];
            }
        }
        ksort($rt);

        $st = array();
        foreach ($rt AS $k => $v) {
            $st[]   = sprintf('%s:%s', $v, $k);
        }

        return implode(';', $st);
    }
    /* }}} */

    /* {{{ private static String load() */
    /**
     * 从DB中加载路由数据
     *
     * @access private static
     * @return String
     */
    private static function load($tbname, $char)
    {
        !self::$inited && self::init();

        return (string)self::$db->getCell(sprintf(
            "SELECT CONCAT(modtime, '|', split_info) FROM %s WHERE tbname='%s' AND routes = '%s' ".
            ' AND idxsign = %u AND useflag IN (%d, %d, %d)',
                '', self::$db->escape($tbname), self::$db->escape($char), self::sign($char . '|' . $tbname),
                self::FLAG_NORMAL_USE, self::FLAG_PRE_RESHIP, self::FLAG_IS_LOCKING
            ));
    }
    /* }}} */

    /* {{{ private static Mixture parse() */
    /**
     * 路由结果解析
     *
     * @access private static
     * @param  String $char
     * @return Mixture
     */
    private static function parse($char)
    {
        list($time, $char) = array_pad(explode('|', trim($char), 2), 2, '');
        $time	= strtotime($time);
        $route	= array();
        foreach (explode("\n", trim($char)) AS $ln) {
            list($node, $name) = explode("\t", $ln);
            $route[]	= array(
                'time'	=> $time,
                'node'	=> $node,
                'name'	=> $name,
            );
        }

        return $route;
    }
    /* }}} */

}
