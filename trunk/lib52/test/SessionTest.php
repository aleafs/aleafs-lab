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

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_SessionTest extends Aleafs_Lib_LibTestShell
{

	protected function setUp()
	{
		parent::setUp();
	}

	protected function tearDown()
    {
		parent::tearDown();
	}

	public function test_should_session_works_fine()
	{
		//XXX: 这个得走HTTP请求来测试
	}

}

