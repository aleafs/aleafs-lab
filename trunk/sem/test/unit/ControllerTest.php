<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | UserTest.php									    				|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: ControllerTest.php 2010-06-01 aleafs Exp $

require_once(__DIR__ . '/../../class/sem/TestShell.php');

class Aleafs_Sem_ControllerTest extends Aleafs_Sem_TestShell
{

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();

        self::registerDefault(__DIR__ . '/ini/global.ini');
        $this->mysql    = new Aleafs_Lib_Db_Mysql('mysql');
        $this->mysql->clear();

        ob_start();
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        @ob_end_clean();
        parent::tearDown();
    }
	/* }}} */

	/* {{{ public void test_should_webui_download_works_fine() */
	public function test_should_webui_download_works_fine()
	{
		$controller	= new Aleafs_Sem_Controller_Webui();

		try {
			$controller->execute('download', array());
			$this->assertTrue(false);
		} catch (Exception $e) {
			$this->assertContains('Machine type is required', $e->getMessage());
		}

		try {
			$controller->execute('download', array('machine' => 'x64'));
			$this->assertTrue(false);
		} catch (Exception $e) {
			$this->assertContains('No such file named as', $e->getMessage());
		}

        $this->mysql->query('DELETE FROM soft_download WHERE ipaddr="unknown"');
        Aleafs_Lib_Context::register('__uagent__', 'PHPUNIT');

        $controller->execute('download', array('machine' => 'x86'));
        $this->mysql->clear()->table('soft_download')->where('ipaddr', 'unknown');
        $this->mysql->select('downcnt','uagent');
        $this->assertEquals($this->mysql->getRow(), array('downcnt' => 1, 'uagent' => 'PHPUNIT'));

        $controller->execute('download', array('machine' => 'x86'));
        $this->mysql->select('downcnt','uagent');
        $this->assertEquals($this->mysql->getRow(), array('downcnt' => 2, 'uagent' => 'PHPUNIT'));
	}
	/* }}} */

}

