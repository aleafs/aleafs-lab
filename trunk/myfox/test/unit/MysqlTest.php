<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\Lib\Mysql;

require_once(__DIR__ . '/../../lib/TestShell.php');

class MysqlTest extends \Myfox\Lib\TestShell
{

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        Mysql::removeAllNames();
        parent::tearDown();
    }
    /* }}} */

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

        $mysql	= new Mysql('', 'mysql3');
        $this->assertEquals(Mysql::instance('MYSQl3'), $mysql);

        Mysql::register('test1', array(
            'dbname'    => 'test',
            'prefix'    => 'myfox_',
        ));
        $mysql  = Mysql::instance('test1');
        $this->assertEquals('test', $mysql->option('dbname'));
        $this->assertEquals('myfox_', $mysql->option('prefix'));
        $this->assertEquals('utf8', $mysql->option('charset'));
    }
    /* }}} */

    /* {{{ public void test_should_simple_query_works_fine() */
    public function test_should_simple_query_works_fine()
    {
        $mysql  = new Mysql(__DIR__ . '/ini/mysql.ini');
        $mysql->addSlave('10.232.64.121', 'magiccube', 'magiccube');
        $mysql->addSlave('10.232.64.121', 'magiccube', 'magiccube');
        $mysql->addMaster('10.232.31.3', 'magiccube', 'magiccube', 3306);

        $rs = $mysql->getGrid($mysql->query('SHOW DATABASES'));
        $this->assertContains(array('Database' => 'test'), $rs);
    }
    /* }}} */

}

