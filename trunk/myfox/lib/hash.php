<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 常用字符串HASH函数														|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: hash.php 4 2010-03-09 05:20:36Z zhangxc83 $

namespace Myfox\Lib;

class Hash
{

	const MAX_INT	= 4294967296;

	/* {{{ public static String md5() */
	/**
	 * 计算MD5
	 *
	 * @access public static
	 * @return String
	 */
	public static function md5($char)
	{
		return bin2hex(md5($char, true));
	}
	/* }}} */

	/* {{{ public static Integer time33() */
	/**
	 * TIME33算法
	 *
	 * @access public static
	 * @return Integer
	 */
	public static function time33($char)
	{
		$sign	= 5381;
		for ($i = 0, $len = strlen($char); $i < $len; $i++) {
			$sign	= ($sign << 5 + $sign) + ord(substr($char, $i, 1));
		}

		return $sign % self::MAX_INT;
	}
	/* }}} */

	/* {{{ public static Integer rotate() */
	/**
	 * 循环算法
	 *
	 * @access public static
	 * @return Integer
	 */
	public static function rotate($char)
	{
		$sign   = strlen($char);
		for ($i = 0, $len = $sign; $i < $len; $i++) {
			$sign   = ($sign << 4) ^ ($sign >> 28) ^ ord(substr($char, $i, 1));
		}

		return $sign % self::MAX_INT;
	}
	/* }}} */

	/* {{{ public static Integer fnvla() */
	public static function fnvla($char)
	{
		$prim	= 16777619;
		$sign	= 0x811C9DC5;
		for ($i = 0, $len = $sign; $i < $len; $i++) {
			$sign	= ($sign ^ ord(substr($char, $i, 1))) * $prim;
		}

		$sign	+= $sign << 13;
		$sign	^= $sign >> 7;
		$sign	+= $sign << 3;
		$sign	^= $sign >> 17;
		$sign	+= $sign << 5;

		return $sign % self::MAX_INT;
	}
	/* }}} */

}
