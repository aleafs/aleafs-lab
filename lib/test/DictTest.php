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

        $this->dfile    = __DIR__ . '/temp/unittest.xdb';
        if (is_file($this->dfile)) {
            @unlink($this->dfile);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /* {{{ public void test_should_create_dict_works_fine() */
    public function test_should_create_dict_works_fine()
    {
        $dict	= new Dict($this->dfile);
        $this->assertFalse($dict->get('key1'));
        $this->assertTrue($dict->set('key1', 2));
        $this->assertEquals(2, $dict->get('key1'));

        $data   = array(
            'a' => 1,
            'b' => array(1,'2',3.000000000000000001),
        );
        $this->assertTrue($dict->set('key2', $data));
        $this->assertEquals($data, $dict->get('key2'));

        // xxx: 二进制数据
        $this->assertTrue($dict->set('key3', md5('test binary data', true)));
        $this->assertEquals(md5('test binary data', true), $dict->get('key3'));

        // xxx: delete
        $this->assertTrue($dict->delete('key2'));
        $this->assertEquals(false, $dict->get('key2'));

        $this->assertTrue($dict->set('key2', ''));
        $this->assertEquals('', $dict->get('key2'));
    }
    /* }}} */

    /* {{{ public void test_should_data_compress_works_fine() */
    public function test_should_data_compress_works_fine()
    {
        $dict   = new Dict($this->dfile);
        $data   = str_repeat('a', 1 + Dict::MIN_GZIP_LEN);
        $this->assertTrue($dict->set('key1', $data));
        $this->assertEquals($data, $dict->get('key1'));

        // xxx : 不压缩，但value需要二次fget
        $data   = str_repeat('a', 21 + Dict::MAX_KEY_LEN);
        $this->assertTrue($dict->set('key1', $data));
        $this->assertEquals($data, $dict->get('key1'));
    }
    /* }}} */

    /* {{{ public void test_should_data_update_works_fine() */
    public function test_should_data_update_works_fine()
    {
        $dict   = new Dict($this->dfile);
        $this->assertTrue($dict->set('key2', 'abcd'));
        $this->assertEquals('abcd', $dict->get('key2'));

        // xxx: 调大
        $this->assertTrue($dict->set('key2', 'abcde'));
        $this->assertEquals('abcde', $dict->get('key2'));

        // xxx: 调小
        $this->assertTrue($dict->set('key2', 'abc'));
        $this->assertEquals('abc', $dict->get('key2'));
    }
    /* }}} */

}

