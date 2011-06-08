<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\Lib\Mysql;

require_once(__DIR__ . '/../../lib/TestShell.php');

class MysqlTest extends \Myfox\Lib\TestShell
{

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        Mysql::removeAllNames();
        parent::tearDown();
    }

    /* {{{ public void test_should_mysql_factory_works_fine() */
    public function test_should_mysql_factory_works_fine()
    {
        try {
            $mysql	= Mysql::instance('i_am_not_exists');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Myfox\Lib\Exception);
            $this->assertContains('Undefined mysql instance named as "i_am_not_exists"', $e->getMessage());
        }

        Mysql::register('mysql1', '');

        $mysql	= new Mysql('', 'mysql3');

        $this->assertEquals(Mysql::instance('MYSQl1'), $mysql);
        $this->assertEquals(Mysql::instance('MYSQl3'), $mysql);
    }
    /* }}} */

}

