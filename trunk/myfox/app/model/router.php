<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: router.php 18 2010-04-13 15:40:37Z zhangxc83 $

namespace Myfox\App\Model;

use \Myfox\Lib\Mysql;
use \Myfox\App\Setting;
use \Myfox\App\Model\Table;

class Router
{

    /* {{{ 静态常量 */

    const FLAG_PRE_IMPORT	= 1;	/**<	路由计算完，等待装载数据	*/
    const FLAG_IMPORT_END	= 2;	/**<	数据装完，等待路由生效		*/
    const FLAG_NORMAL_USE	= 3;	/**<	数据装完，路由生效			*/
    const FLAG_PRE_RESHIP	= 4;	/**<	等待重装					*/
    const FLAG_IS_DELETED	= 0;	/**<	废弃路由，等待删除			*/

    const MIRROR    = 0;            /**<    镜像表 */
    const SHARDING  = 1;            /**<    分区   */

    const ONLINE    = 1;            /**<    正常节点 */
    const ARCHIVE   = 2;            /**<    归档节点 */

    const TABLES_PER_DB = 400;

    /* }}} */

    /* {{{ 静态变量 */

    private static $mysql   = null;

    private static $nodes   = array();

    private static $objects = array();

    /* }}} */

    /* {{{ 成员变量 */

    private $table;

    private $tbname;

    private $rfield = null;

    private $flush  = array();

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
    public static function get($tbname, $field = array(), $touch = false)
    {
        $table  = self::instance($tbname);
        return $table->load($table->filter((array)$field), true, $touch);
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
    public static function set($tbname, $detail = array())
    {
        return self::instance($tbname)->insert((array)$detail);
    }
    /* }}} */

    /* {{{ public static Boolean effect() */
    /**
     * 装完数据，路由生效
     *
     * @access public static
     * @return Boolean true or false
     */
    public static function effect($tbname, $field = array(), $bucket)
    {
    }
    /* }}} */

    /* {{{ public static void clean() */
    /**
     * 清理实例对象 
     *
     * @access public static
     * @return void
     */
    public static function clean()
    {
        self::$objects  = array();
    }
    /* }}} */

    /* {{{ private static Object instance() */
    /**
     * 获取对象实例
     *
     * @access private static
     * @return Object
     */
    private static function instance($tbname)
    {
        if (empty(self::$objects[$tbname])) {
            self::$objects[$tbname] = new self($tbname);
        }

        return self::$objects[$tbname];
    }
    /* }}} */

    /* {{{ private static Mixture nodelist() */
    /**
     * 获取节点列表
     *
     * @access private static
     * @return Mixture
     */
    private static function nodelist($type = 0)
    {
        $type   = (int)$type;
        if (!isset(self::$nodes[$type])) {
            self::$nodes[$type] = (array)self::$mysql->getAll(self::$mysql->query(sprintf(
                'SELECT node_id FROM %snode_list %s ORDER BY node_id ASC',
                self::$mysql->option('prefix', ''),
                !empty($type) ? sprintf(' WHERE node_type = %d', $type) : ''
            )));
        }

        return self::$nodes[$type];
    }
    /* }}} */

    /* {{{ private static String  table() */
    private static function table($route)
    {
        return sprintf('%sroute_info', self::$mysql->option('prefix'));
        return sprintf(
            '%sroute_info_%s',
            self::$mysql->option('prefix'), substr(dechex($route), 0, 1)
        );
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
        $this->table    = Table::instance($tbname);
        if (!$this->table->get('autokid')) {
            throw new \Myfox\Lib\Exception(sprintf(
                'Undefined table named as "%s"', $tbname
            ));
        }
        $this->tbname   = $this->table->get('tabname', '');

        if (empty(self::$mysql)) {
            self::$mysql    = \Myfox\Lib\Mysql::instance('default');
        }
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
        foreach ($this->flush AS $table => $ids) {
            if (empty($ids)) {
                continue;
            }
            self::$mysql->query(sprintf(
                'UPDATE %s SET hittime = %d WHERE autokid IN (%s)',
                $table, time(), implode(',', $ids)
            ));
        }
    }
    /* }}} */

    /* {{{ private String load() */
    /**
     * 从DB中加载路由数据
     *
     * @access private
     * @return String
     */
    private function load($char, $inuse = true, $touch = false)
    {
        $sign   = $this->sign($char);
        $table  = self::table($sign);
        $query  = sprintf(
            "SELECT autokid,modtime,nodes_list,real_table FROM %s WHERE table_name='%s' AND route_text='%s' AND idxsign=%u",
            $table, self::$mysql->escape($this->tbname), self::$mysql->escape($char), $sign
        );

        if (false !== $inuse) {
            $query  = sprintf(
                '%s AND useflag IN (%d,%d)', $query,
                self::FLAG_NORMAL_USE, self::FLAG_PRE_RESHIP
            );
        }

        $routes = array();
        foreach ((array)self::$mysql->getAll(self::$mysql->query($query)) AS $rt) {
            if ($touch) {
                $this->flush[$table][]  = (int)$rt['autokid'];
            }

            $routes[]   = array(
                'tabid' => $table,
                'seqid' => (int)$rt['autokid'],
                'mtime' => (int)$rt['modtime'],
                'node'  => trim($rt['nodes_list'], '{}'),
                'name'  => trim($rt['real_table']),
            );
        }

        return $routes;
    }
    /* }}} */

    /* {{{ private String filter() */
    /**
     * 过滤路由字段
     *
     * @access private
     * @return String
     */
    private function filter($column = array())
    {
        if (null === $this->rfield) {
            $fields = preg_split(
                '/[\s,;\/]+/',
                trim($this->table->get('route_fields', ''), "{}\t\r\n "),
                -1, PREG_SPLIT_NO_EMPTY
            );

            $this->rfield   = array();
            foreach ((array)$fields AS $item) {
                list($name, $type) = array_pad(explode(':', $item, 2), 2, 'int');
                $this->rfield[strtolower(trim($name))]  = strtolower(trim($type));
            }
            ksort($this->rfield);
        }

        $routes = array();
        $column = array_change_key_case((array)$column, CASE_LOWER);
        foreach ($this->rfield AS $name => $type) {
            if (!isset($column[$name])) {
                throw new \Myfox\Lib\Exception(sprintf(
                    'Column "%s" required for table "%s"', $name, $this->tbname
                ));
            }

            $routes[]   = sprintf(
                '%s:%s',
                ('date' == $type) ? date('Ymd', strtotime($column[$name])) : 0 + $column[$name],
                $name
            );
        }

        return implode(';', $routes);
    }
    /* }}} */

    /* {{{ private Integer sign() */
    /**
     * 返回字符串的签名
     * xxx : 此处算法严禁修改
     *
     * @access private
     * @param  String $char
     * @return Integer
     */
    private function sign($char)
    {
        return abs(\Myfox\Lib\Hash::rotate(sprintf(
            '%s|%s', trim($char), $this->tbname
        )));
    }
    /* }}} */

    /* {{{ private Mixture insert() */
    /**
     * 计算路由
     *
     * @access private
     * @return Mixture
     */
    private function insert($detail = array())
    {
        if (self::MIRROR == $this->table->get('route_method')) {
            $nodes  = self::nodelist(0);
            $backup = count($nodes);
            $chunks = array(array(array(
                'data' => '',
                'size' => empty($detail[0]['count']) ? 0 : $detail[0]['count'],
            )));
        } else {
            $nodes  = self::nodelist(self::ONLINE);
            $backup = max(1, $this->table->get('backups'));

            $bucket = new \Myfox\App\Bucket(
                $this->table->get('split_threshold', 2000000),
                $this->table->get('split_drift', 0.2)
            );
            foreach ((array)$detail AS $route) {
                $bucket->push($this->filter($route['field']), $route['count']);
            }
            $chunks = $bucket->allot();
        }

        $counts = count($nodes);
        $last   = (int)Setting::get('last_assign_node');
        $bucket = array();

        $cursor = (int)Setting::get('table_route_count', $this->tbname);
        $dbnums = (int)Setting::get('table_real_count', $this->tbname);

        foreach ($chunks AS $items) {
            $ns = array();
            for ($i = 0; $i < $backup; $i++) {
                $ns[]   = $nodes[($last++) % $counts]['node_id'];
            }

            $ns = implode(',', $ns);
            $tb = sprintf(
                '%s_%d.t_%d_%d', $this->tbname, $dbnums % self::TABLES_PER_DB,
                $this->table->get('autokid'),
                (self::MIRROR === $this->table->get('route_method')) ? $cursor : $cursor % 3
            );
            foreach ($items AS $it) {
                $bucket[$it['data']][]  = array(
                    'rows'  => $it['size'],
                    'node'  => $ns,
                    'table' => $tb,
                );
            }
            $cursor++;
        }

        Setting::set('last_assign_node', $last % $counts);
        Setting::set('table_route_count', sprintf(
            'IF(cfgvalue + 0 > %d, cfgvalue, %d)', $cursor, $cursor
        ), $this->tbname, false);

        $time   = time();
        foreach ($bucket AS $key => $slice) {
            $sign   = $this->sign($key);
            $key    = self::$mysql->escape($key);
            $values = array();
            foreach ($slice AS $rt) {
                $values[]   = sprintf(
                    "(%d,0,%d,%d,'%s','%s','{%s}','%s')",
                    $sign, self::FLAG_PRE_IMPORT, $time, $this->tbname, $key, $rt['node'], $rt['table']
                );
            }

            // xxx: 事务保证
            self::$mysql->query(sprintf(
                "UPDATE %s SET useflag=IF(useflag=%d,%d,%d) WHERE idxsign=%u AND table_name='%s' AND route_text='%s'",
                self::table($sign), self::FLAG_NORMAL_USE, self::FLAG_PRE_RESHIP, self::FLAG_IS_DELETED,
                $sign, $this->tbname, $key
            ));
            self::$mysql->query(sprintf(
                'INSERT INTO %s (idxsign,isarchive,useflag,addtime,table_name,route_text,nodes_list,real_table) VALUES %s',
                self::table($sign), implode(',', $values)
            ));
        }

        return $bucket;
    }
    /* }}} */

}
