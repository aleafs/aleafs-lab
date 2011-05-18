<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Unit Test Case Class												|
// +--------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: TestShell.php 47 2010-04-26 05:27:46Z zhangxc83 $

namespace Myfox\Lib;

require_once(__DIR__ . '/autoload.php');
require_once('PHPUnit/Framework/TestCase.php');

date_default_timezone_set('Asia/Shanghai');

class TestShell extends \PHPUnit_Framework_TestCase
{

	protected function setUp()
	{
		parent::setUp();
		\Myfox\Lib\AutoLoad::init();
    }

}

