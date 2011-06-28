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

    /* {{{ public void test_should_transfer_task_works_fine() */
    public function test_should_transfer_task_works_fine()
    {
        $task	= new \Myfox\App\Task\Transfer(-1, array(
            'from'  => '1,2',
            'save'  => '3',
            'table' => 'numsplit',
            'path'  => 'numsplit_0.numsplit_563_2',
        ));

        $this->assertEquals(Task::WAIT, $task->execute());
        $this->assertEquals(Task::SUCC, $task->wait());
    }
    /* }}} */

    /* {{{ public void test_should_delete_task_works_fine() */
    public function test_should_delete_task_works_fine()
    {
        $task   = new \Myfox\App\Task\Delete(-1, array(
            'host'  => 'host_01_01',
            'path'  => 'mirror_0.t_42_0',
            'where' => '',
        ));
    }
    /* }}} */

    /* {{{ public void test_should_import_task_works_fine() */
    public function test_should_import_task_works_fine()
    {
        $task	= new \Myfox\App\Task\Import(-1, array('a' => 'none'));
    }
    /* }}} */

}
