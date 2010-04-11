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
// $Id$

abstract class Db
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

	/* }}} */

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
		self::NOTLIKE	=> "%s NOT LIKE %%%s%%'",
	);

	/* {{{ 成员变量 */

	/**
	 * @配置属性
	 */
	protected $ini;

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

	public function __construct($ini)
	{
		$this->ini	= $ini;
	}

	public function __destruct()
	{
		if (!empty($this->link)) {
			$this->_disconnect($this->link) && $this->link = null;
		}
	}

	public function clear()
	{
		$this->where	= array();
		$this->order	= array();
		$this->group	= array();
		$this->limit	= array();
		return $this;
	}

	public function table($table)
	{
		$this->table	= (string)$table;
		return $this;
	}

	public function where($name, $value, $mode = self::EQ, $comma = true)
	{
		$this->where[] = array(
			trim($name), $value, $mode, (bool)$comma
		);
		return $this;
	}

	public function order($name, $mode = 'ASC')
	{
		$this->order[trim($name)] = strtoupper($mode);
		return $this;
	}

	public function group($column)
	{
		$this->group[trim($column)] = true;
		return $this;
	}

	public function limit($count, $offset = 0)
	{
		$this->limit = array(max(0, (int)$count), max(0, (int)$offset));
		return $this;
	}

	public function select()
	{
		$column	= func_get_args();
		if (!isset($column[1])) {
			$column	= (array)$column[0];
		}

		$this->datares	= $this->query(sprintf(
			'SELECT %s FROM %s %s %s %s %s',
			self::escape($column, false),
			$this->table,
			$this->_build_where(),
			$this->_build_group(),
			$this->_build_order(),
			$this->_build_limit()
		));

		return $this;
	}

	public function update($value)
	{
		return $this;
	}

	public function insert($value)
	{
		$this->datares	= $this->query(sprintf(
			'INSERT INTO %s (%s) VALUES (%s)',
			$this->table,
			self::escape(array_keys($value), false),
			self::escape($value, true)
		));

		return $this;
	}

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

	public function getAll()
	{
		if (empty($this->datares)) {
			return null;
		}

		$arrRet	= array();
		while ($row = $this->_fetch($this->datares)) {
			$arrRet	= $row;
		}

		return $arrRet;
	}

	public function getOne()
	{
		if (empty($this->datares)) {
			return null;
		}

		return reset($this->_fetch($this->datares));
	}

	public function error()
	{
		if (empty($this->datares)) {
			return null;
		}

		return $this->_error($this->datares);
	}

	public function query($sql)
	{
		if (empty($this->link) && !$this->_connect()) {
			return false;
		}

		return $this->_query(self::sqlClean($sql));
	}

	public function begin()
	{
		if (!$this->transact) {
			$this->_begin();
			$this->transact++;
		}

		return $this;
	}

	public function commit()
	{
		if ($this->transact) {
			$this->_commit();
			$this->transact	= 0;
		}

		return $this;
	}

	public function rollback()
	{
		if ($this->transact) {
			$this->_rollback();
			$this->transact	= 0;
		}

		return $this;
	}

	protected function _build_where()
	{
		$strRet	= '';
		foreach ($this->where AS $where) {
			list($col, $val, $eqs, $com) = $where;
			if (empty($col) || !isset(self::$eqs[$eqs])) {
				continue;
			}

			$com = ($eqs == self::LIKE || $eqs == self::NOTLIKE) ? false : $com;
			$strRet .= sprintf(' AND ' . self::$eqs[$eqs], $col, self::escape($val, $com));
		}

		return $strRet ? sprintf('WHERE %s', substr($strRet, 5)) : '';
	}

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

	protected function _build_group()
	{
		if (empty($this->group)) {
			return '';
		}

		return sprintf('GROUP BY %s', implode(',', array_keys($this->group)));
	}

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

	protected static function sqlClean($sql)
	{
		return trim(preg_replace('/\s{2,}/', ' ', $sql));
	}

	protected static function escape($value, $comma = true)
	{
		if (is_array($value)) {
			$value	= array_unique(array_map(array(self, '_escape'), $value));
			if ($comma) {
				return sprintf("'%s'", implode("','", $value));
			} else {
				return implode(',', $value);
			}
		}

		return sprintf($comma ? "'%s'" : '%s', self::_escape($value));
    }

    private static function _escape($string)
    {
        return addslashes($string);
    }

	abstract protected function _connect();
	abstract protected function _disconnect($link);
	abstract protected function _query($sql);
	abstract protected function _begin();
	abstract protected function _commit();
	abstract protected function _rollback();
	abstract protected function _fetch($res);
	abstract protected function _error($res);

}

