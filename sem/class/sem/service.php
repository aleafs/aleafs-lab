<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | SOAP服务基类	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: service.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Service
{

	/* {{{ 成员变量 */

	protected $authenticated	= false;

	/* }}} */

	/* {{{ public void authenticate() */
	/**
	 * 用户权限验证
	 *
	 * @access public void
	 * @return void
	 */
	public function authenticate($appname, $username, $machine, $nodename)
	{
		$this->authenticated	= true;
	}
	/* }}} */

}

