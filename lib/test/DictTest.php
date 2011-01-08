<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | DictTestShell														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: ApcTest.php 380 2011-01-07 10:18:20Z zhangxc83 $

use \Aleafs\Lib\Dict;

require_once(__DIR__ . '/../class/TestShell.php');

class DictTest extends \Aleafs\Lib\LibTestShell
{

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
	}

	/* {{{ public void test_should_create_dict_works_fine() */
	public function test_should_create_dict_works_fine()
	{
		$file	= __DIR__ . '/temp/test_create.dict';
		@unlink($file);

        $dict	= new Dict($file);
        $this->assertFalse($dict->get('key1'));
        $this->assertTrue($dict->set('key1', 2));
        $this->assertEquals(2, $dict->get('key1'));
	}
	/* }}} */

}

