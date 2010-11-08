<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | DebugTimerTest.php								    				|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: DebugTimerTest.php 2010-06-01 aleafs Exp $

use \Aleafs\Lib\Debug\Timer;

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_DebugTimerTest extends Aleafs_Lib_LibTestShell
{

	protected function setUp()
	{
		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	/* {{{ public void test_should_timer_works_fine() */
	public function test_should_timer_works_fine()
	{
		Aleafs_Lib_Debug_Timer::init(false);
		Aleafs_Lib_Debug_Timer::start('key1');
		$this->assertEquals(null, Aleafs_Lib_Debug_Timer::elapsed('key1'));

		Aleafs_Lib_Debug_Timer::init(true);
		Aleafs_Lib_Debug_Timer::start('key1');
		usleep(300);
		$this->assertEquals(null, Aleafs_Lib_Debug_Timer::elapsed('key2'));

		$this->assertFalse(null == Aleafs_Lib_Debug_Timer::elapsed('Key1 '));
		$this->assertEquals(null, Aleafs_Lib_Debug_Timer::elapsed('key1'));
	}
	/* }}} */

}

