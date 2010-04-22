<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | FactoryTest.php                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;

require_once(__DIR__ . '/../class/TestShell.php');

class FactoryTest extends LibTestShell
{

	protected function setUp()
    {
		parent::setUp();
	}

	protected function tearDown()
	{
        Factory::removeAllObject(true);
		parent::tearDown();
    }

    public function test_should_throw_exception_when_unregister()
    {
        Factory::register('Aleafs\Lib/Log', 'empty', 'log://warn.error/no.log?buffer=98');
        Factory::unregister('AlEAFS/lib/LOG', 'EMPTY');
        try {
            Factory::getObject('Aleafs\Lib\Log', 'empty');
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Aleafs\Lib\Exception);
            $this->assertEquals(
                'Unregistered object name as "empty" for class "Aleafs\Lib\Log"',
                $e->getMessage(),
                'Exception message error.'
            );
        }
    }

    public function test_should_return_right_object()
    {
        AutoLoad::register('aleafs\lib\db', __DIR__ . '/../class/db');
        Factory::register('aleafs\lib\db\sqlite', 'default', __DIR__ . '/factory_test.sqlite', 0766);
        $dao = Factory::getObject('\Aleafs/Lib/Db/Sqlite', 'default');
        $this->assertTrue($dao instanceof \Aleafs\Lib\Db\Sqlite);
    }

}

