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

use \Aleafs\Lib\LibTestShell;
use \Aleafs\Lib\Debug\Timer;

require_once(__DIR__ . '/../class/TestShell.php');

class DebugTimerTest extends LibTestShell
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
		Timer::init(false);
		Timer::start('key1');
		$this->assertEquals(null, Timer::elapsed('key1'));

		Timer::init(true);
		Timer::start('key1');
		usleep(300);
		$this->assertEquals(null, Timer::elapsed('key2'));

		$this->assertFalse(null == Timer::elapsed('Key1 '));
		$this->assertEquals(null, Timer::elapsed('key1'));
	}
	/* }}} */

}

