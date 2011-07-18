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
            "DELETE FROM %ssettings WHERE cfgname IN ('table_route_count', 'table_real_count')",
            self::$mysql->option('prefix')
        ));

        $query  = sprintf("SHOW TABLES LIKE '%sroute_info%%'", self::$mysql->option('prefix'));
        foreach (self::$mysql->getAll(self::$mysql->query($query)) AS $table) {
            self::$mysql->query(sprintf(
                "DELETE FROM %s WHERE table_name IN ('mirror', 'numsplit')", reset($table)
            ));
        }

        Setting::set('last_assign_node', 0);
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_mirror_table_router_set_and_get_works_fine() */
    public function test_should_mirror_table_router_set_and_get_works_fine()
    {
        try {
            Router::set('i am not exists');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Myfox\Lib\Exception);
            $this->assertContains('Undefined table named as "i am not exists"', $e->getMessage());
        }

        $mirror = \Myfox\App\Model\Table::instance('mirror');
        foreach (array(0, 1, 2, 0) AS $key => $id) {
            $this->assertEquals(
                array(
                    ''  => array(
                        array(
                            'rows'  => 1300,
                            'node'  => '1,2,3',
                            'table' => 'mirror_0.t_' . $mirror->get('autokid') . '_' . $id,
                        ),
                    ),
                ),
                Router::set('mirror', array(array('count' => 1300)))
            );
            if ($key == 0) {
                $this->assertEquals(array(), Router::get('mirror'));
            }
        }
        $this->assertEquals(0, Setting::get('last_assign_node'));
        $this->assertEquals(4, Setting::get('table_route_count', 'mirror'));
        $this->assertEquals(0, (int)Setting::get('table_real_count', 'mirror'));

        $where  = Router::table('mirror')->where(null);
        $route  = self::$mysql->getRow(self::$mysql->query(sprintf(
            'SELECT real_table, hittime FROM %s WHERE table_name=\'mirror\' AND useflag=%d ORDER BY autokid DESC LIMIT 1',
            $where['table'], Router::FLAG_PRE_IMPORT
        )));
        $this->assertEquals(0, $route['hittime']);
        $real_table = $route['real_table'];

        // xxx: 模拟数据装完
        Router::effect('mirror', null, $route['real_table']);
        $this->assertEquals(array(), Router::get('mirror'));

        // xxx: 模拟路由生效
        Router::flush();

        $route  = Router::get('mirror', null, true);
        $this->assertEquals(1, count($route));

        $route  = reset($route);
        $this->assertEquals(0, $route['mtime']);
        $this->assertEquals('1,2,3', $route['node']);
        $this->assertEquals($real_table, $route['name']);

        Router::removeAllCache();
        $query  = sprintf(
            'SELECT hittime FROM %s WHERE %s AND useflag=%d ORDER BY autokid DESC LIMIT 1',
            $where['table'], $where['where'], Router::FLAG_NORMAL_USE
        );
        $this->assertEquals(intval(time() / 2), intval(self::$mysql->getOne(self::$mysql->query($query)) / 2));
    }
    /* }}} */

    /* {{{ public void test_should_sharding_table_router_set_and_get_works_fine() */
    public function test_should_sharding_table_router_set_and_get_works_fine()
    {
        $table  = \Myfox\App\Model\Table::instance('numsplit');
        try {
            Router::set('numsplit', array(array(
                'field' => array(
                    'thedate'   => '20110610',
                ),
                'count' => 1201,
            )));
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Myfox\Lib\Exception);
            $this->assertContains('Column "cid" required for table "numsplit"', $e->getMessage());
        }

        $this->assertEquals(
            array(
                '1:cid;20110610:thedate'    => array(
                    array(
                        'rows'  => 1000,
                        'node'  => 1,
                        'table' => 'numsplit_0.t_' . $table->get('autokid') . '_0',
                    ),
                    array(
                        'rows'  => 201,
                        'node'  => 2,
                        'table' => 'numsplit_0.t_' . $table->get('autokid') . '_1',
                    ),
                ),
                '2:cid;20110610:thedate'    => array(
                    array(
                        'rows'  => 998,
                        'node'  => 2,
                        'table' => 'numsplit_0.t_' . $table->get('autokid') . '_1',
                    ),
                ),
            ),
            Router::set('numsplit', array(
                array(
                    'field' => array(
                        'thedate'   => '2011-06-10',
                        'cid'       => 1,
                    ),
                    'count' => 1201,
                ),
                array(
                    'field' => array(
                        'thedate'   => '2011-06-10',
                        'cid'       => 2,
                    ),
                    'count' => 998,
                ),
            )
        ));
        $this->assertEquals(array(), Router::get('numsplit', array(
            'thedate'   => '2011-6-10',
            'cid'       => 1,
            'blablala'  => 2,
        )));

        // xxx: 模拟装完数据
        self::$mysql->query(sprintf(
            "UPDATE %sroute_info SET useflag=%d,modtime=1111 WHERE useflag=%d AND route_text='%s'",
            self::$mysql->option('prefix'), Router::FLAG_NORMAL_USE, Router::FLAG_PRE_IMPORT,
            '1:cid;20110610:thedate'
        ));
        $routes = Router::get('numsplit', array(
            'thedate'   => '2011-6-10',
            'cid'       => 1,
            'blablala'  => 2,
        ));

        $result = array();
        foreach ($routes AS $item) {
            unset($item['tabid'], $item['seqid']);
            $result[]   = $item;
        }

        $this->assertEquals(array(
            array(
                'mtime' => 1111,
                'node'  => 2,
                'name'  => sprintf('numsplit_0.t_%d_1', $table->get('autokid')),
            ),
            array(
                'mtime' => 1111,
                'node'  => 1,
                'name'  => sprintf('numsplit_0.t_%d_0', $table->get('autokid')),
            ),
        ), $result);
    }
    /* }}} */

}
