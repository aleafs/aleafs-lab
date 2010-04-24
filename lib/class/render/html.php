<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Render\Html.php	       											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: html.php 2010-04-23  aleafs Exp $

namespace Aleafs\Lib\Render;

class Html
{

	/* {{{ 静态常量 */

	/**
	 * @用于判断模板文件是否上传完成
	 */
	const TPL_COMPLETE_CHAR	= '<!--COMPLETE-->';

	/* }}} */

	/* {{{ 静态变量 */

	private static $tplDir	= null;

	private static $objDir	= null;

	private static $expire	= 0;

	/* }}} */

	/* {{{ 成员变量 */
	/**
	 * @绑定的数据 
	 */
	private $data	= array();

	/* }}} */

	public function __construct($tpl, $obj, $expire = 0)
	{
		self::$tplDir	= trim($tpl);
		self::$objDir	= trim($obj);
		self::$expire	= (int)$expire;

		$this->removeAllParams();
	}

	public function register($key, $val)
	{
		$this->data[trim($key)] = $val;
	}

	public function unregister($key)
	{
		unset($this->data[trim($key)]));
	}

	public function removeAllParams()
	{
		$this->data	= array();
	}

	public function render($tplName)
	{
	}

	private function compile($tplName)
	{
	}

}

