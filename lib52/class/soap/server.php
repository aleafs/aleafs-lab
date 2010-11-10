<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | SOAP SERVER类		   					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: server.php 22 2010-04-15 16:28:45Z zhangxc83 $

class Aleafs_Lib_Soap_Server
{

	/* {{{ 成员变量 */

	private $wsdl;

	private $password	= null;

    private $charset	= 'utf-8';

    private $soapHeader = null;

	/* }}} */

	/* {{{ public void __construct() */
	/**
	 * 构造函数
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($charset = 'utf-8', $password = null)
	{
		$this->charset	= trim($charset);
		$this->password	= (string)$password;
	}
	/* }}} */

	/* {{{ public void wsdl() */
	/**
	 * 设置WSDL地址
	 *
	 * @access public
	 * @return void
	 */
	public function wsdl($url)
	{
		$this->wsdl	= trim($url);
	}
	/* }}} */

    /* {{{ public void setHeader() */
    /**
     * 设置返回头
     *
     * @access public
     * @return void
     */
    public function setHeader($header)
    {
        $this->soapHeader   = $header;
    }
    /* }}} */

	/* {{{ public void run() */
	/**
	 * SOAP Server运行
	 *
	 * @access public
	 * @return void
	 */
	public function run($class)
    {
		$server	= new SoapServer($this->wsdl, array(
			'soap_version'	=> SOAP_1_2,
			'encoding'		=> $this->charset,
		));

		$data	= file_get_contents('php://input');
		if (!empty($this->password)) {
		}

        if (!empty($this->soapHeader) && ($this->soapHeader instanceof SoapHeader)) {
            $server->addSoapHeader($this->soapHeader);
        }

		$server->setClass($class);
		$server->handle($data);
	}
	/* }}} */

}
