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

class Aleafs_Sem_OptionTest extends Aleafs_Sem_TestShell
{

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();
        self::registerDefault(__DIR__ . '/ini/global.ini');

        Aleafs_Sem_Options::init('mysql', 'sem_options');
        $mysql  = new Aleafs_Lib_Db_Mysql('mysql');
        $mysql->clear()->table('sem_options')->where('cfgname', 'unittest', Aleafs_Lib_Database::LIKE)->delete();
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_option_get_and_set_works_fine() */
    public function test_should_option_get_and_set_works_fine()
    {
        $this->assertEquals(null, Aleafs_Sem_Options::get('unittest_int'));
        $this->assertTrue(Aleafs_Sem_Options::set('unittest_int', 1));
        $this->assertEquals(1, Aleafs_Sem_Options::get('unittest_int'));
        $this->assertTrue(Aleafs_Sem_Options::set('unittest_int', 2));
        $this->assertEquals(2, Aleafs_Sem_Options::get('unittest_int'));

        $data   = array('a' => 'b', 'c' => 20000);
        $this->assertTrue(Aleafs_Sem_Options::set('unittest_array', $data));
        $this->assertEquals($data, Aleafs_Sem_Options::get('unittest_array'));

        $data   = json_decode(json_encode($data));
        $this->assertTrue(Aleafs_Sem_Options::set('unittest_object', $data));
        $this->assertEquals($data, Aleafs_Sem_Options::get('unittest_object'));
    }
    /* }}} */

}
