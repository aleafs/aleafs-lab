<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | SOAP协议处理类	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: autoload.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Controller_Soap extends Aleafs_Lib_Controller
{

	/* {{{ 成员变量 */

	private $server;

	/* }}} */

	/* {{{ public void __construct() */
	/**
	 * 构造函数
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->server	= new Aleafs_Lib_Soap_Server();
	}
	/* }}} */

	/* {{{ protected void actionIndex() */
	/**
	 * 默认ACTION
	 *
	 * @access protected
	 * @return void
	 */
	protected function actionIndex($param, $post = null)
	{
	}
	/* }}} */

	/* {{{ protected void actionAccess() */
	/**
	 * 基本服务请求
	 *
	 * @access protected
	 * @return void
	 */
	protected function actionAccess($param, $post = null)
	{
	}
	/* }}} */

	/* {{{ protected void actionBaidu() */
	/**
	 * 百度服务请求
	 *
	 * @access protected
	 * @return void
	 */
	protected function actionBaidu($param, $post = null)
	{
	}
	/* }}} */

}

