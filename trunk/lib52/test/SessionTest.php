<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | SessionTestShell													|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: SessionTest.php 49 2010-04-27 01:37:36Z zhangxc83 $

namespace Aleafs\Lib;

require_once(__DIR__ . '/../class/TestShell.php');

class SessionTest extends LibTestShell
{

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function test_should_session_read_write_ok()
    {
    }

}

