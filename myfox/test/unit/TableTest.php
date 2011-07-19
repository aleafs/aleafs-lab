<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\App\Model\Table;

require_once(__DIR__ . '/../../lib/TestShell.php');

class TableTest extends \Myfox\Lib\TestShell
{

    private static $mysql;

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();

        \Myfox\Lib\Mysql::register('default', __DIR__ . '/ini/mysql.ini');
        self::$mysql    = \Myfox\Lib\Mysql::instance('default');

        self::$mysql->query(sprintf(
            "REPLACE INTO %s.%stable_list (addtime,modtime,loadtype,tabname, split_threshold,split_drift,route_method,route_fields) VALUES (NOW(),NOW(),1,'mirror',1000,0.2,0,''), (NOW(),NOW(),0,'numsplit',1000,0.2,1,'thedate:date,cid')",
            self::$mysql->option('dbname', 'meta_myfox_config'),
            self::$mysql->option('prefix', '')
        ));
        Table::cleanAllStatic();
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        Table::cleanAllStatic();
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_get_table_option_works_fine() */
    public function test_should_get_table_option_works_fine()
    {
        $table  = Table::instance('numsplit');
        $this->assertEquals(0, $table->queries);

        $this->assertEquals(1000, $table->get('split_threshold'));
        $this->assertEquals(1, $table->queries);

        $this->assertEquals(0.2, $table->get('split_drift'));
        $this->assertEquals(1, $table->queries);

        $this->assertEquals('thedate:date,cid', $table->get('route_fields'));
        $this->assertEquals(1, $table->queries);

        $table  = Table::instance('i am not exists');
        $this->assertEquals(0, $table->queries);

        $this->assertEquals(null, $table->get('split_threshold'));
        $this->assertEquals(1, $table->queries);

        $this->assertEquals(null, $table->get('route_method'));
        $this->assertEquals(1, $table->queries);
    }
    /* }}} */

    /* {{{ public void test_should_table_column_works_fine() */
    public function test_should_table_column_works_fine()
    {
        Table::instance('numsplit')->queries    = 0;
        $column = array();
        foreach ((array)Table::instance('numsplit')->column() AS $key => $opt) {
            $column[$key]   = array(
                'type'      => $opt['coltype'],
                'default'   => $opt['dfltval'],
                'sqlchar'   => $opt['sqlchar'],
            );
        }

        $expect = array(
            'thedate'   => array(
                'type'      => 'date',
                'default'   => '0000-00-00',
                'sqlchar'   => "thedate date not null default '0000-00-00'",
            ),
            'cid'       => array(
                'type'      => 'uint',
                'default'   => '0',
                'sqlchar'   => 'cid int(10) unsigned not null default 0',
            ),
            'num1'       => array(
                'type'      => 'uint',
                'default'   => '0',
                'sqlchar'   => 'num1 int(10) unsigned not null default 0',
            ),
            'num2'       => array(
                'type'      => 'float',
                'default'   => '0.00',
                'sqlchar'   => 'num2 decimal(20,14) not null default 0.00',
            ),
            'char1'       => array(
                'type'      => 'char',
                'default'   => '',
                'sqlchar'   => "char1 varchar(32) not null default ''",
            ),
            'autokid'       => array(
                'type'      => 'uint',
                'default'   => '0',
                'sqlchar'   => "autokid int(10) unsigned not null auto_increment",
            ),
        );

        foreach ($expect AS $key => $opt) {
            $this->assertEquals($opt, $column[$key]);
        }
        $this->assertEquals(1, Table::instance('numsplit')->queries);

        Table::instance('numsplit')->column();
        $this->assertEquals(1, Table::instance('numsplit')->queries);
    }
    /* }}} */

    /* {{{ public void test_should_table_index_works_fine() */
    public function test_should_table_index_works_fine()
    {
        $index  = array();
        foreach ((array)Table::instance('numsplit')->index() AS $key => $opt) {
            $index[$key]    = array(
                'type'  => $opt['idxtype'],
                'char'  => $opt['idxchar'],
            );
        }

        $this->assertEquals(array(
            'idx_split_cid' => array(
                'type'  => '',
                'char'  => 'cid',
            ),
            'pk_split_id'   => array(
                'type'  => 'PRIMARY',
                'char'  => 'autokid',
            ),
        ), $index);
    }
    /* }}} */

}
