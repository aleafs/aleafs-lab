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
// $Id: ConfigerTest.php 58 2010-05-05 00:14:58Z zhangxc83 $

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_ConfigerTest extends Aleafs_Lib_LibTestShell
{

	protected function setUp()
	{
		parent::setUp();
	}

	protected function tearDown()
    {
        Aleafs_Lib_Configer::makeSureRemoveAll();
		parent::tearDown();
	}

    public function test_config_factory_works_fine()
    {
        Aleafs_Lib_Configer::register('test_ini', 'ini://' . __DIR__ . '/config/ini_test.ini');
        Aleafs_Lib_Configer::register('ini_test', 'ini://' . __DIR__ . '/config/ini_test.ini');

        Aleafs_Lib_Configer::unregister('INI_test');

        try {
            Aleafs_Lib_Configer::instance('ini_tesT');
        } catch (Exception $e) {
            $this->assertTrue($e instanceof Aleafs_Lib_Exception);
            $this->assertContains('Undefined config object named as "ini_test"', $e->getMessage());
        }

        $this->assertTrue(Aleafs_Lib_Configer::instance('test_ini') instanceof Aleafs_Lib_Configer);
    }

    public function test_should_ini_file_works_fine()
    {
        Aleafs_Lib_Configer::register('test_ini', __DIR__ . '/config/ini_test.ini');

        $obj = Aleafs_Lib_Configer::instance('test_ini');
        $this->assertEquals(2, $obj->get('i_am_not_exists', 2));

    }

}

