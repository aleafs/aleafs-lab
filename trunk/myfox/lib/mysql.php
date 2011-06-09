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

    /* {{{ 静态常量 */

    const INT	= 'i';
    const CHAR	= 's';
    const FLOAT	= 'd';
    const BLOB  = 'b';

    const MANIPULATE    = '/^(INSERT|REPLACE|DELETE|UPDATE|ALTER|CREATE|DROP|LOAD)\s+/is';

    /* }}} */

    /* {{{ 静态变量 */

    private static $objects	= array();

    private static $alias	= array();

    /* }}} */

    /* {{{ 成员变量 */

    private $master;

    private $slave;

    private $handle;

    private $isMaster   = false;

    private $option = array(
        'timeout'   => 5,
        'persist'   => false,
        'charset'   => 'utf8',
        'dbname'    => '',
        'prefix'    => '',
        'logurl'    => '',
    );

    private $lastId = 0;

    private $error  = '';

    private $log;

    private $stmts  = array();

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
            self::$objects[$name]	= new self(self::$alias[$name], $name);
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
            $mysql->disconnect();
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

        $prefix = empty($name) ? md5(json_encode($config)) : strtolower(trim($name));
        $this->master   = new LiveBox('#MYSQL#' . $prefix . '/master', 10);
        if (!empty($config['master'])) {
            $this->init('addMaster', $config['master']);
        }

        $this->slave    = new LiveBox('#MYSQL#' . $prefix . '/slave', 300);
        if (!empty($config['slave'])) {
            $this->init('addSlave', $config['slave']);
        }

        if (empty($this->option['logurl'])) {
            $this->log  = new \Myfox\Lib\BlackHole();
        } else {
            $this->log  = new \Myfox\Lib\Log($this->option['logurl']);
        }

        $name   = self::normalize($name);
        if (!empty($name) && !isset(self::$objects[$name])) {
            self::$objects[$name]  = &$this;
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
        $this->disconnect();
    }
    /* }}} */

    /* {{{ public void addMaster() */
    /**
     * 添加主库
     *
     * @access public
     * @return void
     */
    public function addMaster($host, $user, $pass, $port = 3306)
    {
        $this->master->register(array(
            'host'  => $host,
            'port'  => $port,
            'user'  => $user,
            'pass'  => $pass,
        ));
    }
    /* }}} */

    /* {{{ public void addSlave() */
    /**
     * 添加从库
     *
     * @access public
     * @return void
     */
    public function addSlave($host, $user, $pass, $port = 3306)
    {
        $this->slave->register(array(
            'host'  => $host,
            'port'  => $port,
            'user'  => $user,
            'pass'  => $pass,
        ));
    }
    /* }}} */

    /* {{{ public Mixture option() */
    /**
     * 获取配置属性
     *
     * @access public
     * @return Mixture
     */
    public function option($key)
    {
        return isset($this->option[$key]) ? $this->option[$key] : null;
    }
    /* }}} */

    /* {{{ public Mixture query() */
    /**
     * 执行query
     *
     * @access public
     * @return Mixture
     */
    public function query($query, $value = null, $type = null)
    {
        $query  = self::sqlclean($query);
        $modify = self::ismodify($query);
        if ($modify) {
            $this->connectToMaster();
        } else {
            $this->connectToSlave();
        }

        if (empty($value)) {
            $rs = $this->handle->query($query);
            if (false !== $rs && true === $modify) {
                $rs = $this->handle->affected_rows;
            }

            $this->error    = $this->handle->error;
            if (0 === strncasecmp('insert', $query, 6)) {
                $this->lastId   = $this->handle->insert_id;
            }
        } else {
            $rs = $this->stmt($query, $value, $type);
        }

        if (false !== $rs) {
            $this->log->debug('QUERY_OK', array(
                'sql'   => $query,
            ));
        } else {
            $this->log->warn('QUERY_ERROR', array(
                'sql'   => $query,
                'error' => $this->error,
            ));
        }

        return $rs;
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
        $query  = self::sqlclean($query);
        if (self::ismodify($query)) {
            $this->connectToMaster();
        } else {
            $this->connectToSlave();
        }
    }
    /* }}} */

    /* {{{ public Mixture poll() */
    /**
     * 等待异步结果
     *
     * @access public
     * @return Mixture
     */
    public function poll()
    {
    }
    /* }}} */

    /* {{{ public Mixture getGrid() */
    /**
     * 获取结果集
     *
     * @access public
     * @return Mixture
     */
    public function getGrid($rs)
    {
        if ($rs instanceof \MySQLi_Result) {
            return self::fetchFromResult($rs, 0);
        }

        if ($rs instanceof \MySQLi_STMT) {
            return self::fetchFromStmt($rs, 0);
        }

        return null;
    }
    /* }}} */

    /* {{{ public void connectToMaster() */
    /**
     * 连接到主库
     *
     * @access public
     * @return void
     */
    public function connectToMaster()
    {
        if (!empty($this->handle) && $this->isMaster) {
            return;
        }

        $this->disconnect();
        $this->connect($this->master, false);
        $this->isMaster = true;
    }
    /* }}} */

    /* {{{ public void connectToSlave() */
    /**
     * 连接到从库
     *
     * @access public
     * @return void
     */
    public function connectToSlave()
    {
        if (!empty($this->handle)) {
            return $this->handle->ping();
        }

        try {
            $this->connect($this->slave);
            $this->isMaster = false;
        } catch (\Exception $e) {
            $this->connectToMaster();
        }
    }
    /* }}} */

    /* {{{ public void disconnect() */
    /**
     * 断开连接
     *
     * @access public
     * @return void
     */
    public function disconnect()
    {
        foreach ($this->stmts AS $pool) {
            if (!empty($pool->stmt)) {
                $pool->stmt->close();
            }
        }
        $this->stmts    = array();

        if (!empty($this->handle)) {
            $this->handle->close();
            $this->handle   = null;
        }
    }
    /* }}} */

    /* {{{ private void init() */
    /**
     * 初始化机器配置
     *
     * @access private
     * @param  String $method
     * @param  Array $host
     * @return void
     */
    private function init($method, $host)
    {
        foreach ((array)$host AS $ln) {
            $cf = parse_url($ln);
            if (empty($cf)) {
                continue;
            }
            $this->$method(
                $cf['host'], rawurldecode($cf['user']), rawurldecode($cf['pass']),
                empty($cf['port']) ? 3306 : $cf['port']
            );
        }
    }
    /* }}} */

    /* {{{ private void connect() */
    /**
     * 连接DB
     *
     * @access private
     * @return void
     */
    private function connect(&$box, $setoff = true)
    {
        do {
            $my = $box->fetch();
            $wr = error_reporting();
            $my['host'] = (empty($this->option['persist']) ? '' : 'p:') . $my['host'];
            error_reporting($wr - E_WARNING);
            foreach (array(10, 100, 1000) AS $us) {
                $is = mysqli_init();
                $is->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->option['timeout']);
                $rs = $is->real_connect($my['host'],$my['user'], $my['pass'], $this->option['dbname'], $my['port']);

                if (false !== $rs) {
                    break;
                }

                $is->kill($is->thread_id);
                $is->close();
                usleep($us);
            }
            error_reporting($wr);

            $my['pass'] = '**';
            if (empty($rs)) {
                $this->log->warn('CONNECT_ERROR', $my + array(
                    'error'     => $is->connect_error,
                ));
                if ($setoff) {
                    $box->setOffline();
                }
            } else {
                $this->log->debug('CONNECT_OK', $my);
                $this->handle   = $is;
                $this->handle->set_charset($this->option['charset']);
                $this->handle->autocommit(true);
            }
        } while (empty($rs));
    }
    /* }}} */

    /* {{{ private Mixture stmt() */
    /**
     * stmt方式执行语句
     *
     * @access private
     * @return Mixture
     */
    private function stmt(&$query, $value, $type = null)
    {
        $id = $query;
        if (!empty($this->stmts[$id])) {
            $stmt   = $this->stmts[$id]->stmt;
            $data   = $this->stmts[$id]->data;
        } else {
            $data   = array();
            if (preg_match_all('/:(\w+)/is', $query, $matches)) {
                foreach ((array)$matches[1] AS $key => $p) {
                    if (!isset($value[$p])) {
                        continue;
                    }

                    $is = 0;
                    if (is_array($value[$p])) {
                        foreach ($value[$p] AS $item) {
                            $tk = sprintf('%s_%d', $p, $is++);
                            $data[]     = $tk;
                            $value[$tk] = $item;
                            $type[$tk]  = isset($type[$p]) ? $type[$p] : self::CHAR;
                        }
                        $is--;
                    } else {
                        $data[] = $p;
                    }

                    $query  = explode(':' . $p, $query, 2);
                    $query  = sprintf('%s%s%s', $query[0], implode(',', array_fill(0, 1 + $is, '?')), $query[1]);
                }
            }

            $stmt   = $this->handle->prepare($query);
            if (empty($stmt)) {
                $this->error    = $this->handle->error;
                return false;
            }

            $this->stmts[$id]   = array(
                'stmt'  => $stmt,
                'data'  => $data,
                'type'  => self::ismodify($query),
            );
        }
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

    /* {{{ private static string sqlclean() */
    /**
     * SQL清洗
     *
     * @access private static
     * @return string
     */
    private static function sqlclean($query)
    {
        return trim(preg_replace('/\s{2,}/', '', $query), "; \t\r\n");
    }
    /* }}} */

    /* {{{ private static Boolean ismodify() */
    /**
     * 是否UPDATE语句
     *
     * @access private static
     * @return Boolean true or false
     */
    private static function ismodify($query)
    {
        return preg_match(self::MANIPULATE, trim($query)) ? true : false;
    }
    /* }}} */

    /* {{{ private static Mixture fetchFromResult() */
    /**
     * 从MySQLi_Result中获取结果
     *
     * @access private static
     * @return Mixture
     */
    private static function fetchFromResult($rs, $cn = 0)
    {
        $rt = array();
        $sz = 0;
        $rs->data_seek(0);
        while (($cn <= 0 || $sz++ < $cn) && $row = $rs->fetch_assoc()) {
            $rt[] = $row;
        }
        $rs->free();

        return $rt;
    }
    /* }}} */

}
