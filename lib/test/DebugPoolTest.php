<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | DebugPoolTest.php								    				|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: DebugPoolTest.php 2010-06-01 aleafs Exp $

use \Aleafs\Lib\LibTestShell;
use \Aleafs\Lib\Debug\Pool;

require_once(__DIR__ . '/../class/TestShell.php');

class DebugPoolTest extends LibTestShell
{

    protected function setUp()
    {
        parent::setUp();
        Pool::clean();
    }

    protected function tearDown()
    {
        Pool::clean();
        parent::tearDown();
    }

    /* {{{ public void test_should_ignore_push_when_debug_not_open() */
    /**
     * 调试关闭时Push不起作用
     * @return void
     */
    public function test_should_ignore_push_when_debug_not_open()
    {
        Pool::openDebug(false);
        $this->assertFalse(Pool::push('test', '啦啦啦啦'));
        $this->assertEquals('NULL', Pool::dump('test'));
    }
    /* }}} */

    /* {{{ public void test_should_push_message_works_fine() */
    /**
     * 测试开启状态下的push功能
     */
    public function test_should_push_message_works_fine()
    {
        Pool::openDebug(true);

        $val = 'debug1';
        Pool::push('test1', $val);
        $this->assertEquals(var_export($val), Pool::dump('test1'));

        $exp = array(
            $val,
            array(
                'text'  => '我是中文',
            ),
        );
        Pool::push('test1', end($exp));
        $this->assertEquals(var_export($exp), Pool::dump('test1'));

        $obj = new Stdclass();
        $obj->val1 = 'key1';
        $obj->val2 = array('啦啦啦');

        Pool::push('test2', $obj);
        $this->assertEquals(var_export($obj), Pool::dump('test2'));

        $exp[] = $obj;
        $this->assertEquals(var_export($exp), Pool::dump());
    }
    /* }}} */

}

