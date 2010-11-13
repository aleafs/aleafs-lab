<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | SOAP Client类		   					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: server.php 22 2010-04-15 16:28:45Z zhangxc83 $

class Aleafs_Lib_Soap_Client
{

	/* {{{ 成员变量 */

	private $wsdl;

	private $header	= array();

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
	}
	/* }}} */

	/* {{{ public Mixture __call() */
	/**
	 * 调用远程接口的魔术方法
	 *
	 * @access public
	 * @return Mixture
	 */
	public function __call($request, $args)
	{
		$client	= new SoapClient($this->wsdl);
		if (!empty($this->header)) {
			$client->__setSoapHeaders($this->soapHeader());
		}

		return $client->__soapCall($request, $args);
	}
	/* }}} */

	/* {{{ protected Mixture soapHeader() */
	/**
	 * 组织SOAP头信息
	 *
	 * @access protected
	 * @return Mixture
	 */
	protected function soapHeader()
	{
		if (is_object($this->header)) {
			$header	= $this->header;
		} else {
			$header	= json_decode(json_encode($this->header));
		}

		return array(new SoapHeader(
			null,
			'version',
			$header,
			false,
			SOAP_ACTOR_NEXT
		));
	}
	/* }}} */

}

