<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | ConfigerTest.php						    						|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: SessionTest.php 58 2010-05-05 00:14:58Z zhangxc83 $

require_once(__DIR__ . '/../../class/sem/TestShell.php');

class Aleafs_Sem_MysqlSessionTest extends Aleafs_Sem_TestShell
{

	protected function setUp()
	{
		parent::setUp();
        self::registerDefault(__DIR__ . '/ini/global.ini');
	}

	protected function tearDown()
    {
		parent::tearDown();
	}

	public function test_should_session_mysql_store_works_fine()
    {
        $store  = new Aleafs_Lib_Session_Mysql('mysql/web_session');
	}

}

