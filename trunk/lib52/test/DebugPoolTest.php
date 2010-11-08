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

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_DebugPoolTest extends Aleafs_Lib_LibTestShell
{

    protected function setUp()
    {
        parent::setUp();
        Aleafs_Lib_Debug_Pool::clean();
    }

    protected function tearDown()
    {
        Aleafs_Lib_Debug_Pool::clean();
        parent::tearDown();
    }

    /* {{{ public void test_should_ignore_push_when_debug_not_open() */
    /**
     * 调试关闭时Push不起作用
     * @return void
     */
    public function test_should_ignore_push_when_debug_not_open()
    {
        Aleafs_Lib_Debug_Pool::openDebug(false);
        $this->assertFalse(Aleafs_Lib_Debug_Pool::push('test', '啦啦啦啦'));
        $this->assertEquals('NULL', Aleafs_Lib_Debug_Pool::dump('test'));
    }
    /* }}} */

    /* {{{ public void test_should_push_message_works_fine() */
    /**
     * 测试开启状态下的push功能
     */
    public function test_should_push_message_works_fine()
    {
        Aleafs_Lib_Debug_Pool::openDebug(true);

        $val = 'debug1';
        Aleafs_Lib_Debug_Pool::push('test1', $val);
        $this->assertEquals(var_export($val, true), Aleafs_Lib_Debug_Pool::dump('test1'));

        $exp = array(
            $val,
            array(
                'text'  => '我是中文',
            ),
        );
        Aleafs_Lib_Debug_Pool::push('test1', end($exp));
        $this->assertEquals(var_export($exp, true), Aleafs_Lib_Debug_Pool::dump('test1'));

        $obj = new Stdclass();
        $obj->val1 = 'key1';
        $obj->val2 = array('啦啦啦');

        Aleafs_Lib_Debug_Pool::push('test2', $obj);
        $this->assertEquals(var_export($obj, true), Aleafs_Lib_Debug_Pool::dump('test2'));

        $exp = array(
            'test1' => $exp,
            'test2' => $obj,
        );
        $this->assertEquals(var_export($exp, true), Aleafs_Lib_Debug_Pool::dump());
    }
    /* }}} */

}

