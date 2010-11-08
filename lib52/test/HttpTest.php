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
// $Id: HttpTest.php 65 2010-05-13 16:03:54Z zhangxc83 $

namespace Aleafs\Lib;

require_once(__DIR__ . '/../class/TestShell.php');

class HttpTest extends LibTestShell
{

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        usleep(1050000);          /**<  避免apc写入时出现warning      */
    }

    public function test_should_get_and_post_work_fine()
    {
        $http = new Http(array(
            'server'    => array(
                'localhost?weight=1',
                '127.0.0.1:80?weight=2'
            ),
        ), 'test');

        try {
            $http->get('/lib_test/http_test.php?a=1&b=');
        } catch (\Exception $e) {
        
        }
    }

}

