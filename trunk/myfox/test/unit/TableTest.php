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

    public function test_should_table_column_works_fine()
    {
        $table  = Table::instance('numsplit');
        print_r($table->column());
    }

}
