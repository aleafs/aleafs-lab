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
// $Id$

namespace Aleafs\Lib;

require_once(__DIR__ . '/../class/TestShell.php');

class ConfigerTest extends LibTestShell
{

	protected function setUp()
	{
		parent::setUp();
	}

	protected function tearDown()
    {
        Configer::makeSureRemoveAll();
		parent::tearDown();
	}

    public function test_config_factory_works_fine()
    {
        Configer::register('test_ini', 'ini://' . __DIR__ . '/config/ini_test.ini');
        Configer::register('ini_test', 'ini://' . __DIR__ . '/config/ini_test.ini');

        Configer::unregister('INI_test');

        try {
            Configer::instance('ini_tesT');
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Aleafs\Lib\Exception);
            $this->assertContains('Undefined config object named as "ini_test"', $e->getMessage());
        }

        $this->assertTrue(Configer::instance('test_ini') instanceof Configer);
    }

    public function test_should_ini_file_works_fine()
    {
        Configer::register('test_ini', __DIR__ . '/config/ini_test.ini');

        $obj = Configer::instance('test_ini');
        $this->assertEquals(2, $obj->get('i_am_not_exists', 2));

    }

}

