<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | UserTest.php									    				|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: UserTest.php 2010-06-01 aleafs Exp $

require_once(__DIR__ . '/../../class/sem/TestShell.php');

class Aleafs_Sem_UserTest extends Aleafs_Sem_TestShell
{

    protected function setUp()
    {
        parent::setUp();
        self::registerDefault(__DIR__ . '/ini/global.ini');

        Aleafs_Sem_User::cleanPermission('unittest', 'baidu');
    }

    protected function tearDown()
    {
        parent::tearDown();
	}

    /* {{{ public void test_should_insert_select_permission_works_fine() */
    public function test_should_insert_select_permission_works_fine()
    {
    }
    /* }}} */

}
