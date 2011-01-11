<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 业务缓存封装类        												|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: cache.php 2010-05-29 aleafs Exp $

namespace Aleafs\Lib;

class Cache
{

	/* {{{ 静态常量 */

	const MCACHE	= 1;
	const APC		= 2;
	const FILE		= 4;

	/* }}} */

	/* {{{ 静态变量 */

	private static $objects	= array();

	/* }}} */

	/* {{{ 成员变量 */

	private $handle;

	/* }}} */

	/* {{{ public static Object instance() */
	/**
	 * 获取缓存实例
	 *
	 * @access public static
	 * @return Mixture object
	 */
	public static function instance($name, $type = self::APC)
	{
	}
	/* }}} */

	/* {{{ public Mixture get() */
	/**
	 * 获取CACHE数据
	 *
	 * @access public
	 * @return Mixture
	 */
	public function get($key, $tm = false)
	{
		return $this->filter($this->handle->get($key, $tm));
	}
	/* }}} */

	/* {{{ public Boolean set() */
	/**
	 * 添加/更新数据
	 *
	 * @access public
	 * @return Boolean true or false
	 */
	public function set()
	{
	}
	/* }}} */

	/* {{{ public Boolean add() */
	/**
	 * 添加数据
	 *
	 * @access public
	 * @return Boolean true or false
	 */
	public function add()
	{
	}
	/* }}} */

	/* {{{ public Boolean delete() */
	/**
	 * 删除缓存
	 *
	 * @access public
	 * @return Boolean true or false
	 */
	public function delete($key)
	{
		$this->handle->delete($key);
	}
	/* }}} */

}

