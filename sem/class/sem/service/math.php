<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 测试服务处理类	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: math.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Service_Math extends Aleafs_Sem_Service
{

	/* {{{ public void AuthHeader() */
	public function AuthHeader($params)
	{
		file_put_contents("auth.txt", $params->username."\t".$params->password."\t".$params->token);
		$this->authenticated	= true;
	}
	/* }}} */

	/* {{{ public Mixture add() */
	public function add($params)
	{
		$server = Aleafs_Lib_Context::get('soap.server');
		if (!empty($server)) {
			$server->addSoapHeader(new SoapHeader(
				sprintf('%s/soap/math', Aleafs_Lib_Context::get('webroot')),
				'ResHeader', 
				array(
					'status'	=> 1,
					'error'		=> 'fuck',
				)
			));
		}

		$res	= 10;
		if (!empty($this->authenticated)) {
			$res	= $params->a + $params->b;
		}

		return array('sum' => $res);
	}
	/* }}} */

}

