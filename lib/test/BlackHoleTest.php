<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | SessionTestShell													|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: ApcTest.php 63 2010-05-12 07:40:08Z zhangxc83 $

use \Aleafs\Lib\Blackhole;

require_once(__DIR__ . '/../class/TestShell.php');

class BlackHoleTest extends \Aleafs\Lib\LibTestShell
{

	public function test_should_blackhole_works_fine()
	{
		$object	= new Blackhole();

		$object->lalala	= '我不起作用的';

		$this->assertNull($object->lalala);
		$this->assertNull($object->helloworld());
		$this->assertNull(Blackhole::staticHello());
	}

}


