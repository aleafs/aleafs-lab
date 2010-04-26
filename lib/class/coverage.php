<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 代码覆盖率类	        											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: apc.php 45 2010-04-24 15:52:47Z zhangxc83 $

namespace Aleafs\Lib;

class Coverage
{

	private static $outfile;

	private static $killer;

	private static $xdebug;

	public static function init($outfile = null)
	{
		self::$outfile	= $outfile;
		self::$xdebug	= extension_loaded('xdebug') ? true : false;

		if (self::$xdebug) {
			xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
			self::$killer	= new CoverageKiller();
		}
	}

	public static function flush()
	{
		if (!self::$xdebug || !empty($outfile)) {
			return;
		}

		file_put_contents(self::$outfile, serialize(xdebug_get_code_coverage()));
		xdebug_stop_code_coverage();
	}

}

class CoverageKiller
{
	public function __destruct()
	{
		Coverage::flush();
	}
}

