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

        $data   = array(
            'a' => 1,
            'b' => array(1,'2',3.000000000000000001),
        );
        $this->assertTrue($dict->set('key2', $data));
        $this->assertEquals($data, $dict->get('key2'));

        // xxx: 测试新值较短的情况
        $this->assertTrue($dict->set('key2', 100));
        $this->assertEquals(100, $dict->get('key2'));

        // xxx: 二进制数据
        $this->assertTrue($dict->set('key3', md5('test binary data', true)));
        $this->assertEquals(md5('test binary data', true), $dict->get('key3'));
	}
	/* }}} */

}

