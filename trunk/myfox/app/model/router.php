<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: router.php 18 2010-04-13 15:40:37Z zhangxc83 $

namespace Myfox\App\Model;

use \Myfox\Lib\Mysql;

class Router
{

	/* {{{ 静态常量 */

	const FLAG_PRE_IMPORT	= 1;	/**<	路由计算完，等待装载数据	*/
	const FLAG_IMPORT_END	= 2;	/**<	数据装完，等待路由生效		*/
	const FLAG_NORMAL_USE	= 3;	/**<	数据装完，路由生效			*/
	const FLAG_PRE_RESHIP	= 4;	/**<	等待重装					*/
	const FLAG_IS_LOCKING	= 5;	/**<	热数据迁移时使用			*/
	const FLAG_IS_DELETED	= 0;	/**<	废弃路由，等待删除			*/

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
		return self::parse(self::load(sprintf(
			'%s:%s', $tbname, json_encode($field)
		)));
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

	/* {{{ private static String load() */
	/**
	 * 从DB中加载路由数据
	 *
	 * @access private static
	 * @return String
	 */
	private static function load($char)
	{
		!self::$inited && self::init();
		$ln	= self::$db->getRow(sprintf(
			"SELECT modtime,split_info FROM %s WHERE idxsign = %u AND routes = '%s' AND useflag IN (%d, %d, %d)",
			'',	self::sign($char), self::$db->escape($char),
			self::FLAG_NORMAL_USE, self::FLAG_PRE_RESHIP, self::FLAG_IS_LOCKING
		));

		if (empty($ln)) {
			return '';
		}

		return sprintf('%s|%s', $ln['modtime'], $ln['split_info']);
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
