<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\App\Model\Router;
use \Myfox\App\Setting;

require_once(__DIR__ . '/../../lib/TestShell.php');

class RouterTest extends \Myfox\Lib\TestShell
{

    private static $mysql;

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();

        \Myfox\Lib\Mysql::register('default', __DIR__ . '/ini/mysql.ini');
        self::$mysql    = \Myfox\Lib\Mysql::instance('default');
        self::$mysql->query(sprintf(
            "DELETE FROM %stable_list WHERE tabname = 'i am not exists'",
            self::$mysql->option('prefix')
        ));
        self::$mysql->query(sprintf(
            "DELETE FROM %sroute_info WHERE tabname IN ('mirror', 'numsplit')",
            self::$mysql->option('prefix')
        ));

        Setting::set('last_assign_node', 0);
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_router_set_and_get_works_fine() */
    public function test_should_router_set_and_get_works_fine()
    {
        try {
            Router::set('i am not exists');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Myfox\Lib\Exception);
            $this->assertContains('Undefined table named as "i am not exists"', $e->getMessage());
        }

        return;
        $this->assertEquals(
            array(
                array(
                    'rows'  => 1300,
                    'node'  => '1,2,3',
                    'table' => '',
                ),
            ),
            Router::set('mirror', array('thedate' => 20110610), 1300)
        );
        $this->assertEquals(0, Setting::get('last_assign_node'));
    }
    /* }}} */

}
