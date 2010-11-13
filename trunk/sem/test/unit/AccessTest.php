<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | AccessTest.php									    				|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: AccessTest.php 2010-06-01 aleafs Exp $

require_once(__DIR__ . '/../../class/sem/TestShell.php');

class Aleafs_Sem_AccessTest extends Aleafs_Sem_TestShell
{

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();
        self::registerDefault(__DIR__ . '/ini/global.ini');
        Aleafs_Sem_User::cleanPermission('functest', 'baidu');
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_trial_user_auto_register_works_fine() */
    public function test_should_trial_user_auto_register_works_fine()
    {
        $server = new Aleafs_Sem_Service_Access();
        $this->assertFalse($server->authenticated);

        $header = array(
            'appname'   => 'baidu',
            'username'  => 'i_am_not_exists',
            'machine'   => 'abcdefg',
            'nodename'  => 'aleafs-lab',
        );

        $server->AuthHeader(json_decode(json_encode($header)));
        $this->assertFalse($server->authenticated);
        $this->assertEquals(array(), $server->permission());

        $header['username'] = 'functest';
        $server->AuthHeader(json_decode(json_encode($header)));
        $this->assertTrue($server->authenticated);
        $this->assertEquals(array(
            array(
                'unit'      => 'BASE',
                'type'      => Aleafs_Sem_User::PERM_TRIAL,
                'begdate'   => date('Y-m-d'),
                'enddate'   => date('Y-m-d', strtotime('+299 day')),
                'balance'   => 300,
            ),
        ), $server->permission());

        $this->assertEquals(array(
            'function'  => '',
            'args'      => '',
        ), $server->heartbeat());
    }
    /* }}} */

}
