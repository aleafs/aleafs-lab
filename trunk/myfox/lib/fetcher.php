<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 文件获取类		    					    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>	    							|
// +------------------------------------------------------------------------+
//
// $Id: fetcher.php 22 2010-04-15 16:28:45Z zhangxc83 $

namespace Myfox\Lib;

class Fetcher
{

	/* {{{ 成员变量 */

	private $source;

	private $target;

	private $error;

	/* }}} */

	/* {{{ public void __construct() */
	/**
	 * 构造函数
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($url, $path = '/tmp')
	{
		$this->source	= trim($url);
		$this->target	= trim($path);
	}
	/* }}} */

	/* {{{ public Boolean getfile() */
	/**
	 * 获取文件
	 *
	 * @access public
	 * @return Boolean true or false
	 */
	public function getfile(&$file)
	{
		$option	= parse_url($this->source);
		if (empty($option) || empty($option['scheme'])) {
			if (!is_file($this->source)) {
				$this->error	= sprintf('No such file named as "%s"', $this->source);
				return false;
			}
			$file	= realpath($this->source);
			return true;
		}

		$class	= sprintf(
			'%s\%s', __CLASS__, 
			ucfirst(strtolower(trim($option['scheme'])))
		);
		try {
			$ob	= new $class($option);
		} catch (\Exception $e) {
			$this->error	= $e->getMessage();
			return false;
		}
	}
	/* }}} */

	/* {{{ public String lastError() */
	/**
	 * 获取错误信息
	 *
	 * @access public
	 * @return String
	 */
	public function lastError()
	{
		return $this->error;
	}
	/* }}} */

}

