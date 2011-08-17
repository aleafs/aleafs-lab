<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | FTP获取文件	    					    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>	    							|
// +------------------------------------------------------------------------+
//
// $Id: fileset.php 22 2010-04-15 16:28:45Z zhangxc83 $

namespace Myfox\Lib\Fetcher;

class Ftp
{

	/* {{{ 成员变量 */

	private $lastError	= '';

	/* }}} */

	/* {{{ public void __construct() */
	/**
	 * 构造函数
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($option)
	{
	}
	/* }}} */

	/* {{{ public Boolean fetch() */
	/**
	 * 获取文件
	 *
	 * @access public
	 * @return Boolean true or false
	 */
	public function fetch($fname, $cache = true)
	{
		return true;
	}
	/* }}} */

	/* {{{ public Mixture lastError() */
	/**
	 * 获取错误描述
	 *
	 * @access public
	 * @return Mixture
	 */
	public function lastError()
	{
		return $this->lastError;
	}
	/* }}} */

}
