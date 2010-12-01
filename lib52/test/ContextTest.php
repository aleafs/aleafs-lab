<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | ContextTest															|
// +------------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: context.lib.php 4 2010-03-09 05:20:36Z zhangxc83 $

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_ContextTest extends Aleafs_Lib_LibTestShell
{

    protected function setUp()
    {
        parent::setUp();
		Aleafs_Lib_Context::cleanAllContext();
    }

    protected function tearDown()
    {
		Aleafs_Lib_Context::cleanAllContext();
        parent::tearDown();
	}

	/* {{{ public void test_should_register_and_get_works_fine() */
	public function test_should_register_and_get_works_fine()
	{
		Aleafs_Lib_Context::register('key1', 'val1');
		Aleafs_Lib_Context::register('key2', new StdClass());
		Aleafs_Lib_Context::unregister('key2');
		Aleafs_Lib_Context::unregister('I_am_not_exists');
		$this->assertEquals('val1', Aleafs_Lib_Context::get('key1', 'val2'));
		$this->assertEquals('default', Aleafs_Lib_Context::get('key2', 'default'));
	}
	/* }}} */

	/* {{{ public void test_should_get_correct_pid() */
	public function test_should_get_correct_pid()
	{
		$this->assertEquals(getmypid(), Aleafs_Lib_Context::pid());
	}
	/* }}} */

	/* {{{ public void test_should_get_correct_userip() */
	public function test_should_get_correct_userip()
	{
		$_SERVER	= array();
		$this->assertEquals('unknown', Aleafs_Lib_Context::userip());
		$this->assertEquals(0, Aleafs_Lib_Context::userip(true));

		$_SERVER['REMOTE_ADDR']	= '127.0.0.1';
		$this->assertEquals('unknown', Aleafs_Lib_Context::userip());
		$this->assertEquals(0, Aleafs_Lib_Context::userip(true));

		Aleafs_Lib_Context::cleanAllContext();
		$this->assertEquals('127.0.0.1', Aleafs_Lib_Context::userip());
		$this->assertEquals(ip2long('127.0.0.1'), Aleafs_Lib_Context::userip(true));

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '202.111.111.123,59.66.192.112';
		Aleafs_Lib_Context::cleanAllContext();
		$this->assertEquals('59.66.192.112', Aleafs_Lib_Context::userip());
		$this->assertEquals(ip2long('59.66.192.112'), Aleafs_Lib_Context::userip(true));
	}
	/* }}} */

    /* {{{ public void test_should_get_uagent_works_fine() */
    public function test_should_get_uagent_works_fine()
    {
        Aleafs_Lib_Context::register('__uagent__', 'PHPUNIT');
        $this->assertEquals('PHPUNIT', Aleafs_Lib_Context::uagent());
    }
    /* }}} */

}

