<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\App\Task;

require_once(__DIR__ . '/../../lib/TestShell.php');

class TaskTest extends \Myfox\Lib\TestShell
{

	private static $mysql;

	protected function setUp()
	{
		parent::setUp();

		\Myfox\Lib\Mysql::register('default', __DIR__ . '/ini/mysql.ini');
		self::$mysql    = \Myfox\Lib\Mysql::instance('default');
	}

	/* {{{ public void test_should_import_task_works_fine() */
	/**
	 * @xxx: 这个啥都不干哦
	 */
	public function test_should_import_task_works_fine()
	{
		$task	= new \Myfox\App\Task\Import(-1, array('a' => 'none'));

		$this->assertEquals('none', $task->option('a'));

		$this->assertFalse($task->lock());
		//$this->assertEquals(Task::FAIL, $task->execute());
		//$this->assertEquals(Task::FAIL, $task->wait());
		$this->assertFalse($task->unlock());

		//$this->assertContains('Unreachable interface', $task->getLastError());
	}
	/* }}} */

}
