<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | DB操作类		    					    							|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id: db.php 19 2010-04-14 02:00:51Z zhangxc83 $

abstract class Aleafs_Lib_Database
{

    /* {{{ 静态常量 */

    const EQ		= 1;
    const NE		= 2;
    const GT		= 3;
    const GE		= 4;
    const LT		= 5;
    const LE		= 6;
    const IN		= 7;
    const NOTIN		= 8;
    const LIKE		= 9;
    const NOTLIKE	= 10;

    /**
     * @短路边界
     */
    const CIRCUIT   = 3;

    /* }}} */

    /* {{{ 静态变量 */

    private static $eqs	= array(
        self::EQ		=> '%s = %s',
        self::NE		=> '%s != %s',
        self::GT		=> '%s > %s',
        self::GE		=> '%s >= %s',
        self::LT		=> '%s < %s',
        self::LE		=> '%s <= %s',
        self::IN		=> '%s IN (%s)',
        self::NOTIN		=> '%s NOT IN (%s)',
        self::LIKE		=> "%s LIKE '%%%s%%'",
        self::NOTLIKE	=> "%s NOT LIKE '%%%s%%'",
    );
    /* }}} */

    /* {{{ 成员变量 */

    /**
     * @配置属性
     */
    protected $ini;

    /**
     * @连接短路
     */
    protected $circuit  = 0;

    /**
     * @事务
     */
    protected $transact	= 0;

    /**
     * @连接句柄
     */
    protected $link		= null;

    /**
     * @数据资源
     */
    protected $datares	= null;

    /**
     * @当前操作表
     */
    protected $table	= null;
    /**
     * @WHERE条件
     */
    protected $where	= array();

    /**
     * @ORDER
     */
    protected $order	= array();

    /**
     * @LIMIT
     */
    protected $limit	= array();

    /**
     * @GROUP
     */
    protected $group	= array();

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct($ini)
    {
        $this->ini	= $ini;
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
        if (is_resource($this->datares)) {
            $this->_free();
        }
        $this->datares = null;

        if (!empty($this->link)) {
            $this->rollback();
            $this->_disconnect();
            $this->link = null;
        }
    }
    /* }}} */

    /* {{{ public Object clear() */
    /**
     * 清理SQL属性
     *
     * @access public
     * @return Object $this
     */
    public function clear()
    {
        $this->where	= array();
        $this->order	= array();
        $this->group	= array();
        $this->limit	= array();
        return $this;
    }
    /* }}} */

    /* {{{ public Object table() */
    /**
     * 设置当前操作表
     *
     * @access public
     * @param  String $table : 表名
     * @return Object $this
     */
    public function table($table)
    {
        $this->table	= (string)$table;
        return $this;
    }
    /* }}} */

    /* {{{ public Object where() */
    /**
     * 设置过滤条件
     *
     * @access public
     * @param  String $name : 字段名
     * @param  Mixture $value : 值
     * @param  Integer $mode : 过滤方式
     * @param  Boolean $comma : 是否加引号
     * @return Object $this
     */
    public function where($name, $value, $mode = self::EQ, $comma = true)
    {
        $this->where[] = array(
            trim($name), $value, $mode, (bool)$comma
        );
        return $this;
    }
    /* }}} */

    /* {{{ public Object order() */
    /**
     * 设置排序方式
     *
     * @access public
     * @param  String $name : 列名
     * @param  String $mode : ASC / DESC
     * @return Object $this
     */
    public function order($name, $mode = 'ASC')
    {
        $this->order[trim($name)] = strtoupper(trim($mode));
        return $this;
    }
    /* }}} */

    /* {{{ public Object group() */
    /**
     * 设置类聚方式
     *
     * @access public
     * @param  String $column
     * @return Object $this
     */
    public function group($column)
    {
        $this->group[trim($column)] = true;
        return $this;
    }
    /* }}} */

    /* {{{ public Object limit() */
    /**
     * 设置条数限制条件
     *
     * @access public
     * @param  Integer $count : 结果数
     * @param  Integer $offset : 偏移量
     * @return Object $this
     */
    public function limit($count, $offset = null)
    {
        $this->limit = array(max(0, (int)$count));
        if (null !== $offset) {
            $this->limit[] = max(0, (int)$offset);
        }
        return $this;
    }
    /* }}} */

    /* {{{ public Object select() */
    /**
     * 执行检索
     *
     * @access public
     * @return Object $this
     */
    public function select()
    {
        $column	= func_get_args();
        if (!isset($column[1])) {
            $column	= (array)$column[0];
        }

        $this->datares	= $this->query(sprintf(
            'SELECT %s FROM %s %s %s %s %s',
            implode(',', $column),
            $this->table,
            $this->_build_where(),
            $this->_build_group(),
            $this->_build_order(),
            $this->_build_limit()
        ));

        return $this;
    }
    /* }}} */

    /* {{{ public Object update() */
    /**
     * 执行更新
     *
     * @access public
     * @param  Mixture $value : 键值数组
     * @param  Mixture $comma : 是否加引号
     * @return Object $this
     */
    public function update($value, $comma = null)
    {
        $values = '';
        $comma  = (array)$comma;
        foreach ((array)$value AS $key => $val) {
            $key = trim($key);
            if (empty($key)) {
                continue;
            }
            $values .= sprintf(
                ',%s=%s', $key,
                $this->escape($val, !isset($comma[$key]) || false !== $comma[$key])
            );
        }

        if (empty($values)) {
            $this->_free();
            return $this;
        }

        $this->datares  = $this->query(sprintf(
            'UPDATE %s SET %s %s %s %s',
            $this->table,
            substr($values, 1),
            $this->_build_where(),
            $this->_build_order(),
            $this->_build_limit()
        ));

        return $this;
    }
    /* }}} */

    /* {{{ public Object insert() */
    /**
     * 执行插入
     *
     * @access public
     * @param  Mixture $value
     * @return Object $this
     */
    public function insert($value)
    {
        $this->datares	= $this->query(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(',', array_keys($value)),
            $this->escape($value, true)
        ));

        return $this;
    }
    /* }}} */

    /* {{{ public Object delete() */
    /**
     * 执行删除
     *
     * @access public
     * @return Object $this
     */
    public function delete()
    {
        $this->datares	= $this->query(sprintf(
            'DELETE FROM %s %s %s %s',
            $this->table,
            $this->_build_where(),
            $this->_build_order(),
            $this->_build_limit()
        ));

        return $this;
    }
    /* }}} */

    /* {{{ public Mixture getAll() */
    /**
     * 获取查询结果集
     *
     * @access public
     * @return Mixture
     */
    public function getAll($res = null)
    {
        $res    = empty($res) ? $this->datares : $res;
        if (empty($res)) {
            return null;
        }

        $arrRet	= array();
        while ($row = $this->_fetch($res)) {
            $arrRet	= $row;
        }

        return $arrRet;
    }
    /* }}} */

    /* {{{ public Mixture getRow() */
    /**
     * 获取查询结果集第一行
     *
     * @access public
     * @return Mixture
     */
    public function getRow($res = null)
    {
        $res    = empty($res) ? $this->datares : $res;
        if (empty($res)) {
            return null;
        }

        return $this->_fetch($res);
    }
    /* }}} */

    /* {{{ public Mixture getOne() */
    /**
     * 获取第一个单元格的数据
     *
     * @access public
     * @return Mixture
     */
    public function getOne($res = null)
    {
        $res    = empty($res) ? $this->datares : $res;
        if (empty($res)) {
            return null;
        }

        $temp   = $this->_fetch($res);
        return reset($temp);
    }
    /* }}} */

    /* {{{ public Mixture error() */
    /**
     * 获取最后一次错误信息
     *
     * @access public
     * @return Mixture
     */
    public function error()
    {
        return $this->_error();
    }
    /* }}} */

    /* {{{ public Mixture query() */
    /**
     * 执行一条SQL
     *
     * @access public
     * @param  String $sql
     * @return Mixture
     */
    public function query($sql, $try = true)
    {
        if (empty($this->link)) {
            if ($this->circuit >= self::CIRCUIT) {
                return false;
            }

            $error = true;
            for ($i = 0; $i < 3; $i++) {
                if (false !== $this->_connect()) {
                    $error = false;
                    break;
                }
                usleep(($i + 1) * 10000);
            }
            $this->circuit++;

            if ($error) {
                return false;
            }
        }

        return $this->_query(self::sqlClean($sql), $try);
    }
    /* }}} */

    /* {{{ public Object begin() */
    /**
     * 开启事务
     *
     * @access public
     * @return Object $this
     */
    public function begin()
    {
        if (!$this->transact) {
            $this->_begin();
            $this->transact++;
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Object commit() */
    /**
     * 提交事务
     *
     * @access public
     * @return Object $this
     */
    public function commit()
    {
        if ($this->transact) {
            $this->_commit();
            $this->transact	= 0;
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Object rollback() */
    /**
     * 回滚事务
     *
     * @access public
     * @return Object $this
     */
    public function rollback()
    {
        if ($this->transact) {
            $this->_rollback();
            $this->transact	= 0;
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Integer lastId() */
    /**
     * 返回上一次子增列插入的值
     *
     * @access public
     * @return Integer
     */
    public function lastId()
    {
        return empty($this->link) ? 0 : (int)$this->_lastId();
    }
    /* }}} */

    /* {{{ public Integer numRows() */
    /**
     * 返回查询获得结果数
     *
     * @access public
     * @return Integer
     */
    public function numRows()
    {
        return empty($this->datares) ? 0 : (int)$this->_numRows();
    }
    /* }}} */

    /* {{{ public Integer affectedRows() */
    /**
     * 返回更新受影响的行数
     *
     * @access public
     * @return Integer
     */
    public function affectedRows()
    {
        return empty($this->link) ? 0 : (int)$this->_affectedRows();
    }
    /* }}} */

    /* {{{ protected String _build_where() */
    /**
     * 构造where子句
     *
     * @access protected
     * @return String
     */
    protected function _build_where()
    {
        $strRet	= '';
        foreach ($this->where AS $where) {
            list($col, $val, $eqs, $com) = $where;
            if (empty($col) || !isset(self::$eqs[$eqs])) {
                continue;
            }

            $com = ($eqs == self::LIKE || $eqs == self::NOTLIKE) ? false : $com;
            $strRet .= sprintf(' AND ' . self::$eqs[$eqs], $col, $this->escape($val, $com));
        }

        return $strRet ? sprintf('WHERE %s', substr($strRet, 5)) : '';
    }
    /* }}} */

    /* {{{ protected String _build_order() */
    /**
     * 构造order子句
     *
     * @access protected
     * @return String
     */
    protected function _build_order()
    {
        $strRet	= '';
        foreach ($this->order AS $name => $type) {
            if (empty($name)) {
                continue;
            }
            $strRet	.= sprintf(' %s %s', $name, $type == 'DESC' ? 'DESC' : 'ASC');
        }

        return empty($strRet) ? '' : 'ORDER BY ' . $strRet;
    }
    /* }}} */

    /* {{{ protected String _build_group() */
    /**
     * 构造group子句
     *
     * @access protected
     * @return String
     */
    protected function _build_group()
    {
        if (empty($this->group)) {
            return '';
        }

        return sprintf('GROUP BY %s', implode(',', array_keys($this->group)));
    }
    /* }}} */

    /* {{{ protected String _build_limit() */
    /**
     * 构造limit子句
     *
     * @access protected
     * @return String
     */
    protected function _build_limit()
    {
        if (empty($this->limit)) {
            return '';
        }

        $strRet	= reset($this->limit);	/* count  */
        if (count($this->limit) > 1) {	/* offset */
            $strRet = sprintf('%d, %d', next($this->limit), $strRet);
        }

        return sprintf('LIMIT %s', $strRet);
    }
    /* }}} */

    /* {{{ protected static String sqlClean() */
    /**
     * SQL语句清洗
     *
     * @access protected static
     * @param  String $sql
     * @return String
     */
    protected static function sqlClean($sql)
    {
        return trim(preg_replace('/\s{2,}/', ' ', $sql));
    }
    /* }}} */

    /* {{{ private String escape() */
    /**
     * 安全过滤并打包
     *
     * @access protected static
     * @param  Mixture $value
     * @param  Boolean $comma (default true)
     * @return String
     */
    private function escape($value, $comma = true)
    {
        if (is_array($value)) {
            $value	= array_unique(array_map(array(&$this, '_escape'), $value));
            if ($comma) {
                return sprintf("'%s'", implode("','", $value));
            } else {
                return implode(',', $value);
            }
        }

        return sprintf($comma ? "'%s'" : '%s', $this->_escape($value));
    }
    /* }}} */

    /* {{{ private String _escape() */
    /**
     * 安全过滤函数
     *
     * @access private static
     * @param  String $string
     * @return String
     */
    private function _escape($string)
    {
        return addslashes($string);
    }
    /* }}} */

    abstract protected function _connect();
    abstract protected function _disconnect();
    abstract protected function _query($sql);
    abstract protected function _free();
    abstract protected function _begin();
    abstract protected function _commit();
    abstract protected function _rollback();
    abstract protected function _fetch($res);
    abstract protected function _error();
    abstract protected function _lastId();
    abstract protected function _numRows();
    abstract protected function _affectedRows();

}

