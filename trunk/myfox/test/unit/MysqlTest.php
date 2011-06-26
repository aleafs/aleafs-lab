<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\Lib\Config;
use \Myfox\Lib\Mysql;

require_once(__DIR__ . '/../../lib/TestShell.php');

class MysqlTest extends \Myfox\Lib\TestShell
{

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();

        $config = new Config(__DIR__ . '/ini/mysql_test.ini');
        $logurl = parse_url($config->get('logurl', ''));

        $this->logfile  = $logurl['path'];
        @unlink($this->logfile);

        Mysql::removeAllNames();
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        @unlink($this->logfile);
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
        $mysql  = new Mysql(__DIR__ . '/ini/mysql_test.ini');
        try {
            $mysql->connectToSlave();
        } catch (\Exception $e) {
            $this->assertContains(
                "\tCONNECT_ERROR\t-\t{\"host\":\"127.0.0.1\",\"port\":3306,\"user\":\"user_ro\",\"pass\":\"**\",\"error\":",
                self::getLogContents($this->logfile, -1)
            );
        }

        $mysql->addSlave('10.232.64.121', 'magiccube', 'magiccube');
        $mysql->addMaster('10.232.31.3', 'magiccube', 'magiccube', 3306);
        $mysql->connectToSlave();
        $this->assertContains(
            "\tCONNECT_OK\t-\t{\"host\":\"10.232.64.121\",\"port\":3306,\"user\":\"magiccube\",\"pass\":\"**\"",
            self::getLogContents($this->logfile, -1)
        );

        $rs = $mysql->getAll($mysql->query('SHOW DATABASES'));
        $this->assertContains("\tQUERY_OK\t-\t{\"sql\":\"SHOW DATABASES\"", self::getLogContents($this->logfile, -1));
        $this->assertContains(array('Database' => 'test'), $rs);

        $this->assertFalse($mysql->query('I AM A WRONG QUERY'));
        $this->assertContains(
            "\tQUERY_ERROR\t-\t{\"sql\":\"I AM A WRONG QUERY\",\"async\":false,\"error\":",
            self::getLogContents($this->logfile, -1)
        );

        $this->assertEquals(1, $mysql->query('INSERT INTO meta_myfox_cluster.only_for_test (content) VALUES ("aabbcc")'));
        $this->assertContains(
            "\tCONNECT_OK\t-\t{\"host\":\"10.232.31.3\",\"port\":3306,\"user\":\"magiccube\",\"pass\":\"**\"",
            self::getLogContents($this->logfile, -2)
        );

        $lastId = $mysql->lastId();
        $this->assertEquals($lastId, $mysql->getOne($mysql->query(
            'SELECT MAX(id) FROM meta_myfox_cluster.only_for_test'
        )));
        $mysql->query('INSERT INTO meta_myfox_cluster.only_for_test (content) VALUES ("aabbcc2")');
        $this->assertTrue($lastId < $mysql->lastId());

        $mysql->query('TRUNCATE meta_myfox_cluster.only_for_test');
    }
    /* }}} */

    /* {{{ public void test_should_escape_works_fine() */
    public function test_should_escape_works_fine()
    {
        $mysql  = new Mysql(array(
            'charset'   => 'UTF8',
            'persist'   => true,
            'logurl'    => 'log://debug.notice.warn.error' . $this->logfile . '?buffer=0',
            'master'    => array(
                'mysql://magiccube:magiccube@10.232.31.3',
            ),
        ));

        $data   = array(
            'a' => 'i\'m chinese',
            "'" => '省',
        );

        $this->assertEquals(array(
            'a' => 'i\\\'m chinese',
            '\\\''  => '省',
        ), $mysql->escape($data));
        $this->assertContains(
            "\tCONNECT_OK\t-\t{\"host\":\"p:10.232.31.3\",\"port\":3306,\"user\":\"magiccube\",\"pass\":\"**\"",
            self::getLogContents($this->logfile, -1)
        );
    }
    /* }}} */

    public function test_should_async_query_works_fine()
    {
        $mysql  = new Mysql(__DIR__ . '/ini/mysql_test.ini');
        $mysql->addSlave('10.232.64.121', 'magiccube', 'magiccube');
        $mysql->addMaster('10.232.31.3', 'magiccube', 'magiccube', 3306);
        $a1 = $mysql->async('SELECT MAX(id) FROM meta_myfox_cluster.only_for_test');
        $this->assertEquals($a1, 0);
        //var_dump($a1, $mysql);

        $mysql->wait($a1);
    }

}

