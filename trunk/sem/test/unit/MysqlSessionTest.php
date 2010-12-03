<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | ConfigerTest.php						    						|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: SessionTest.php 58 2010-05-05 00:14:58Z zhangxc83 $

require_once(__DIR__ . '/../../class/sem/TestShell.php');

class Aleafs_Sem_MysqlSessionTest extends Aleafs_Sem_TestShell
{

	protected function setUp()
	{
		parent::setUp();
        self::registerDefault(__DIR__ . '/ini/global.ini');

        $mysql  = new Aleafs_Lib_Db_Mysql('mysql');
        $mysql->clear()->table('web_session')->where('sesskey', 'unittest')->delete();
	}

	protected function tearDown()
    {
		parent::tearDown();
	}

	public function test_should_session_mysql_store_works_fine()
    {
        $store  = new Aleafs_Lib_Session_Mysql('mysql/web_session');
        $this->assertNull($store->get('unittest', $attr));
        $this->assertEquals(array(), $attr);

        $data   = array(
            'a' => 'b',
            'c' => decbin(10),
        );
        $attr   = array(
            Aleafs_Lib_Session::TS  => time(),
            Aleafs_Lib_Session::IP  => 1,
        );
        $this->assertEquals(1, $store->set('unittest', $data, array_merge($attr, array(
            'i_am_not_exists'   => 'vip',
        ))));
        $this->assertEquals($data, $store->get('unittest', $newa));
        $this->assertEquals($newa, $attr);

        $this->assertEquals(1, $store->set('unittest', $data + array(Aleafs_Lib_Session::IP => 2), $attr));
        $this->assertEquals(1, $store->delete('unittest'));
        $this->assertNull($store->get('unittest', $attr));

        $this->assertEquals(1, $store->set('unittest', $data, $attr));
        $store->gc(time() + 10);
        $this->assertNull($store->get('unittest', $attr));
	}

}

