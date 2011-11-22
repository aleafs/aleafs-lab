<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\App\Queque;
use \Myfox\App\Task;

require_once(__DIR__ . '/../../lib/TestShell.php');

class QuequeTest extends \Myfox\Lib\TestShell
{

    private static $mysql;

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();

        \Myfox\Lib\Mysql::register('default', __DIR__ . '/ini/mysql.ini');
        self::$mysql    = \Myfox\Lib\Mysql::instance('default');

        self::$mysql->query(sprintf(
            'TRUNCATE TABLE %s.%stask_queque',
            self::$mysql->option('dbname', 'meta_myfox_config'),
            self::$mysql->option('prefix', '')
        ));
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_queque_insert_and_fetch_works_fine() */
    public function test_should_queque_insert_and_fetch_works_fine()
    {
        $queque = Queque::instance();
        $this->assertEquals(null, $queque->fetch());
        $this->assertTrue($queque->insert(
            Task::IMPORT, array(
                'src' => 'http://www.taobao.com',
            ),
            1,
            array(
                'trytimes'  => 2,
                'adduser'   => 'unittest',
                'priority'  => 201,
            )
        ));

        $task   = Task::create($queque->fetch(1, null, false));
        $this->assertTrue($task instanceof \Myfox\App\Task\Import);
        $this->assertEquals('http://www.taobao.com', $task->option('src',''));

        $this->assertTrue($queque->insert(
            Task::TRANSFER, array(
                'from'  => 1,
                'to'    => 9,
            ),
            1,
            array(
                'adduser'   => 'unittest',
                'trytimes'  => 2,
            )
        ));
        $this->assertTrue($queque->insert(
            Task::TRANSFER, array(
                'from'  => 2,
                'to'    => 10,
            ),
            0,
            array(
                'adduser'   => 'unittest',
                'trytimes'  => 1,
            )
        ));

        $task   = Task::create($queque->fetch(1));
        $this->assertTrue($task instanceof \Myfox\App\Task\Transfer);
        $this->assertEquals(2, $task->option('from'));
        $this->assertEquals(10, $task->option('to'));

        $this->assertEquals(1, $queque->update($task->id, array(
            'trytimes'  => 'trytimes + 1',
            'priority'  => 202,
        ), array(
            'trytimes'  => true,
        )));

        $this->assertEquals(array(
            'trytimes'  => 2,
            'priority'  => 202,
        ), self::$mysql->getRow(self::$mysql->query(sprintf(
            'SELECT trytimes, priority FROM %s.%stask_queque WHERE autokid = %d',
            self::$mysql->option('dbname', 'meta_myfox_config'),
            self::$mysql->option('prefix', ''),
            $task->id
        ))));
    }
    /* }}} */

}

