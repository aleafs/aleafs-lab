<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Unit Test Case Class												|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: TestShell.php 47 2010-04-26 05:27:46Z zhangxc83 $

require_once(__DIR__ . '/autoload.php');
require_once('PHPUnit/Framework/TestCase.php');

date_default_timezone_set('Asia/Shanghai');

class Aleafs_Lib_LibTestShell extends PHPUnit_Framework_TestCase
{

	protected function setUp()
	{
		parent::setUp();
		Aleafs_Lib_AutoLoad::init();
    }

}

