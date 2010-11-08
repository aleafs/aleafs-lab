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
// $Id: coverage.php 48 2010-04-26 15:58:11Z zhangxc83 $

class Aleafs_Lib_Debug_Coverage
{

    /* {{{ 静态变量 */

    private static $outfile;

    private static $killer;

    private static $xdebug;

    /* }}} */

    /* {{{ public static void init() */
    /**
     * 初始化xdebug工具
     *
     * @access public static
     * @param  String $outfile : 输出文件
     * @return void
     */
    public static function init($outfile = null)
    {
        self::$outfile	= $outfile;
        self::$xdebug	= extension_loaded('xdebug') ? true : false;

        if (self::$xdebug) {
            xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
            self::$killer	= new Aleafs_Lib_Debug_CoverageKiller();
        }
    }
    /* }}} */

    /* {{{ public static void flush() */
    /**
     * 输出xdebug信息
     *
     * @access public static
     * @return void
     */
    public static function flush()
    {
        if (!self::$xdebug || !empty($outfile)) {
            return;
        }

        file_put_contents(self::$outfile, serialize(xdebug_get_code_coverage()));
        xdebug_stop_code_coverage();
    }
    /* }}} */

}

/* {{{ class CoverageKiller() */

class Aleafs_Lib_Debug_CoverageKiller
{
    public function __destruct()
    {
        Aleafs_Lib_Debug_Coverage::flush();
    }
}
/* }}} */

