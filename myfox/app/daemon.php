<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 后台运行daemon类						    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: daemon.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

namespace Myfox\App;

class Daemon
{

	/* {{{ public static void run() */
	/**
	 * 进入工作模式
	 *
	 * @access public static
	 * @return void
	 */
	public static function run($ini, $class, $args = null)
	{
	}
	/* }}} */

	/* {{{ public static void setAutoLoad() */
	/**
	 * 自动加载初始化
	 *
	 * @access public static
	 * @return void
	 */
	public static function setAutoLoad()
	{
		require_once __DIR__ . '/../lib/autoload.php';

		\Myfox\Lib\AutoLoad::init();
		\Myfox\Lib\AutoLoad::register('myfox\\app',    __DIR__);
	}
	/* }}} */

	/* {{{ private void __construct() */
	/**
	 * 构造函数
	 *
	 * @access private
	 * @return void
	 */
	private function __construct($ini)
	{
	}
	/* }}} */

}
