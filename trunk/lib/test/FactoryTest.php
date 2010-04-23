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

    /* {{{ public void test_should_throw_exception_when_unregister() */
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
    /* }}} */

    /* {{{ public void test_should_return_right_object() */
    public function test_should_return_right_object()
    {
        AutoLoad::register('factory',   __DIR__ . '/factory');
        Factory::register('factory\person', 'lucy', 'Kent', 28);

        $obj = Factory::getObject('Factory\Person', 'lucy');
        $this->assertTrue($obj instanceof \Factory\Person, 'Object is not a Factory\Persion.');

        $this->assertEquals(28, $obj->age, 'Lucy\'s age is not 28');
        $this->assertEquals('Kent', $obj->name, 'Lucy\'s name is not Kent.');

        $obj->setAge(27);
        $this->assertEquals(27, Factory::getObject('Factory\Person', 'lucy')->age, 'Lucy\'s age is not 27, but I have modified it.');
    }
    /* }}} */

}

