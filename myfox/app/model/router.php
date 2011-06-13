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
    const FLAG_IS_LOCKING	= 5;	/**<	热数据迁移时使用			*/
    const FLAG_IS_DELETED	= 0;	/**<	废弃路由，等待删除			*/

    const MIRROR    = 0;            /**<    镜像表 */
    const SHARDING  = 1;            /**<    分区   */

    const ONLINE    = 1;            /**<    正常节点 */
    const ARCHIVE   = 2;            /**<    归档节点 */

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
        $table  = self::instance($tbname);
        $routes = $table->load($table->filter((array)$field));
        if (empty($routes)) {
            return null;
        }

        return array(
            'seqid' => sprintf('%d_%d', $routes['tabid'], $routes['seqid']),
            'mtime' => $routes['mtime'],
            'route' => self::parse($routes['route']),
        );
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

        return $sign % 4294967296;
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
        if (empty($char)) {
            return null;
        }

        $route	= array();
        foreach (explode("\n", trim($char)) AS $ln) {
            $ln = explode("\t", $ln);
            if (empty($ln[1])) {
                continue;
            }
            $route[]	= array(
                'node'	=> $ln[0],
                'name'	=> $ln[1],
            );
        }

        return $route;
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

    /* {{{ private String load() */
    /**
     * 从DB中加载路由数据
     *
     * @access private
     * @return String
     */
    private function load($char)
    {
        $query  = sprintf(
            "SELECT autokid,modtime,split_info FROM %s%%s WHERE tabname='%s' AND routes='%s' AND idxsign=%u AND useflag IN (%d,%d,%d)",
            self::$mysql->option('prefix', ''),
            self::$mysql->escape($this->tbname),
            self::$mysql->escape($char),
            self::sign($char . '|' . $this->tbname),
            self::FLAG_NORMAL_USE, self::FLAG_PRE_RESHIP, self::FLAG_IS_LOCKING
        );

        foreach (array('route_info') AS $key => $table) {
            $rt = self::$mysql->getRow(self::$mysql->query(sprintf($query, $table)));
            if (!empty($rt)) {
                return array(
                    'tabid' => $key,
                    'seqid' => (int)$rt['autokid'],
                    'mtime' => strtotime($rt['modtime']),
                    'route' => $rt['split_info'],
                );
            }
        }

        return null;
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
                throw new \Myfox\Lib\Exception('Column "%s" required for table "%s"', $name, $this->tbname);
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

        foreach ($chunks AS $items) {
            $ns = array();
            for ($i = 0; $i < $backup; $i++) {
                $ns[]   = $nodes[($last++) % $counts]['node_id'];
            }

            $ns = implode(',', $ns);

            // xxx: 表名
            $tb = '';
            foreach ($items AS $it) {
                $bucket[$it['data']][]  = array(
                    'rows'  => $it['size'],
                    'node'  => $ns,
                    'table' => $tb,
                );
            }
        }
        Setting::set('last_assign_node', $last % $counts);

        // xxx: write to db

        return $bucket;
    }
    /* }}} */

}
