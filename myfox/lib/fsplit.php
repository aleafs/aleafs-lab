<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 文件获取类		    					    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>	    							|
// +------------------------------------------------------------------------+
//
// $Id: fileset.php 22 2010-04-15 16:28:45Z zhangxc83 $

namespace Myfox\Lib;

class Fsplit
{

	/* {{{ 成员变量 */

	private $nline	= 1000;

	private $bsize	= 0;

	/* }}} */

    /* {{{ public static Mixture chunk() */
    /**
     * 按行切分文件
     *
     * @access public static
     * @return Mixture
     */
    public static function chunk($fname, $slice, $nline = 1000, $bsize = 0)
	{
		$ob	= new self($nline, $bsize);
		return $ob->split($fname, $slice);
    }
    /* }}} */

	private function __construct($nline, $bsize)
	{
	}

	private function split($fname, $slice)
	{
	}

}
