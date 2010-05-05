<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | HttpTest.php												|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;
require_once(__DIR__ . '/../class/TestShell.php');

class HttpTest extends TestShell
{

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function test_should_get_and_post_work_fine()
    {
        $http = new Http(array(
            'server'    => array(
                'localhost?weight=1',
                '127.0.0.1:80?weight=2'
            ),
        ), 'test');

        $http->get('/lib_test/http_test.php?a=1&b=');
    }

}
