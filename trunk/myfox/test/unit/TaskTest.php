<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\App\Task;

require_once(__DIR__ . '/../../lib/TestShell.php');

class TaskTest extends \Myfox\Lib\TestShell
{

    private static $mysql;

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();

        \Myfox\Lib\Mysql::register('default', __DIR__ . '/ini/mysql.ini');
        self::$mysql    = \Myfox\Lib\Mysql::instance('default');
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ private static Boolean create_test_table() */
    private static function create_test_table($host, $table, $like)
    {
        self::drop_test_table($host, $table);
        return \Myfox\App\Model\Server::instance($host)->getlink()->query(sprintf(
            'CREATE TABLE %s LIKE %s', $table, $like
        ));
    }
    /* }}} */

    /* {{{ private static Boolean drop_test_table() */
    private static function drop_test_table($host, $table)
    {
        return (bool)\Myfox\App\Model\Server::instance($host)->getlink()->query(sprintf(
            'DROP TABLE IF EXISTS %s', $table
        ));
    }
    /* }}} */

    /* {{{ private static Boolean check_table_exists() */
    private static function check_table_exists($host, $table)
    { 
        return (bool)\Myfox\App\Model\Server::instance($host)->getlink()->query(sprintf(
            'DESC %s', $table
        ));
    }
    /* }}} */

    /* {{{ public void test_should_example_task_works_fine() */
    public function test_should_example_task_works_fine()
    {
        $task	= new \Myfox\App\Task\Example(-1, array('a' => 'none'));
        $this->assertEquals('none', $task->option('a'));
        $this->assertEquals(Task::FAIL, $task->execute());
        $this->assertContains('Required column named as "type"', $task->lastError());

        $task	= new \Myfox\App\Task\Example(-1, array('type' => 'none'));
        $this->assertEquals(0, $task->lock());

        $this->assertEquals(0, $task->counter);
        $this->assertEquals(Task::SUCC, $task->execute());
        $this->assertEquals(1, $task->counter);

        $this->assertEquals(Task::FAIL, $task->wait());
        $this->assertContains('None sense for wait', $task->lastError());
        $this->assertEquals(0, $task->unlock());
    }
    /* }}} */

    /* {{{ public void test_should_delete_task_works_fine() */
    public function test_should_delete_task_works_fine()
    {
        $task   = new \Myfox\App\Task\Delete(-1, array(
            'path'  => 'mirror_0.t_42_0',
            'where' => '',
        ));
        $this->assertEquals(Task::FAIL, $task->execute());

        self::create_test_table('edp1_9801', 'mirror_0.task_test', 'mirror_0.mirror_583_2');
        self::create_test_table('edp2_9902', 'mirror_0.task_test', 'mirror_0.mirror_583_2');

        $this->assertEquals(true,   self::check_table_exists('edp1_9801', 'mirror_0.task_test'));
        $this->assertEquals(true,   self::check_table_exists('edp2_9902', 'mirror_0.task_test'));

        $task   = new \Myfox\App\Task\Delete(-1, array(
            'host'  => '1,3,-1,2,1',
            'path'  => 'mirror_0.task_test',
            'where' => '',
        ));
        $this->assertEquals(Task::WAIT, $task->execute());
        $this->assertEquals(Task::SUCC, $task->wait());
        $this->assertEquals('edp1_9801,edp2_9902', $task->result());
        $this->assertEquals(false,  self::check_table_exists('edp1_9801', 'mirror_0.task_test'));
        $this->assertEquals(false,  self::check_table_exists('edp2_9902', 'mirror_0.task_test'));

        // xxx: 带WHERE条件的
        self::create_test_table('edp1_9801', 'mirror_0.task_test', 'mirror_0.mirror_583_2');

        $task   = new \Myfox\App\Task\Delete(-1, array(
            'host'  => '1,3,-1,2,1',
            'path'  => 'mirror_0.task_test',
            'where' => '1=1 AND 0 < 2',
        ));
        $this->assertEquals(Task::WAIT, $task->execute());

        // xxx: host_02_01 上不存在
        $this->assertEquals(Task::FAIL, $task->wait());
        $this->assertContains("Table 'mirror_0.task_test' doesn't exist", $task->lastError());

        $this->assertEquals(true,   self::check_table_exists('edp1_9801', 'mirror_0.task_test'));
        $this->assertEquals(false,  self::check_table_exists('edp2_9902', 'mirror_0.task_test'));
    }
    /* }}} */

    /* {{{ public void test_should_transfer_task_works_fine() */
    public function test_should_transfer_task_works_fine()
    {
        self::drop_test_table('edp2_8510', 'mirror_0.task_test');
        self::drop_test_table('edp2_9902', 'mirror_0.task_test');

        self::create_test_table('edp1_9801', 'mirror_0.task_test', 'mirror_0.mirror_583_2');

        $task	= new \Myfox\App\Task\Transfer(-1, array(
            'from'  => '1,-1,1',
            'save'  => '3,4,3,2',
            'table' => 'mirror',
            'path'  => 'mirror_0.task_test',
            'copy'  => true,
        ));

        $this->assertEquals(Task::WAIT, $task->execute());
        $this->assertEquals(Task::SUCC, $task->wait());

        // xxx: host_02_01 没有装federated
        //$this->assertEquals(true, self::check_table_exists('host_03_01', 'mirror_0.task_test'));
        $this->assertEquals(true, self::check_table_exists('edp2_8510', 'mirror_0.task_test'));

        //$this->assertEquals('', $task->result());
    }
    /* }}} */

    /* {{{ public void test_should_transfer_exception_works_fine() */
    public function test_should_transfer_exception_works_fine()
    {
        $task   = new \Myfox\App\Task\Transfer(-1, array(
            'table' => 'mirror'
        ));
        $this->assertEquals(Task::FAIL, $task->execute());

        $task	= new \Myfox\App\Task\Transfer(-1, array(
            'from'  => '1,2,-1,1',
            'save'  => '3,8',
            'table' => 'i am not exists',
            'path'  => 'mirror_0.task_test',
            'copy'  => true,
        ));
        $this->assertEquals(Task::IGNO, $task->execute());
        $this->assertContains('Undefined table named as "i am not exists"', $task->lastError());

        /* xxx: empty source */
        $task	= new \Myfox\App\Task\Transfer(-1, array(
            'from'  => '-1,10',
            'save'  => '1,2',
            'table' => 'mirror',
            'path'  => 'mirror_0.task_test',
            'copy'  => true,
        ));
        $this->assertEquals(Task::FAIL, $task->execute());
        $this->assertContains('Empty transfer source servers, input:-1,10', $task->lastError());

        // xxx: empty target
        $task	= new \Myfox\App\Task\Transfer(-1, array(
            'from'  => '1,2,-1,1',
            'save'  => '-1,10000',
            'table' => 'mirror',
            'path'  => 'mirror_0.task_test',
            'copy'  => true,
        ));
        $this->assertEquals(Task::IGNO, $task->execute());
        $this->assertContains('Empty transfer target servers, input:-1,10000', $task->lastError());
    }
    /* }}} */

    /* {{{ public void test_should_import_task_works_fine() */
    public function test_should_import_task_works_fine()
    {
        $task	= new \Myfox\App\Task\Import(-1, array('a' => 'none'));
    }
    /* }}} */

}
