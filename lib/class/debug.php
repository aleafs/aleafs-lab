<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Debug 工具类														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;

class Debug
{

	private static $open  = false;

	private static $debug = array();

	public static function openDebug($open)
	{
		self::$open = (bool)$open;
	}

	public static function &register($cfgName,$cfgValue)
	{
		if (self::$open) {
			self::$debug[$cfgName] = $cfgValue;
		}

		return self::$debug;
	}

	public static function getData()
	{
		if (!isset(self::$debug['data'])) {
			return '';
		}

		return var_export(self::$debug['data'], true);
	}

	public static function getDebug()
	{
		$ret = '';
		foreach (self::$debug AS $key => $val) {
			if ($key == 'data') {
				continue;
			}

			$ret .= sprintf("%s : %s\n", $key, var_export($val, true));
		}

		return $ret;
	}

	public static function clean() 
	{
		self::$debug = array();
	}

}

