<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\App\Model\Server;

require_once(__DIR__ . '/../../lib/TestShell.php');

class ServerTest extends \Myfox\Lib\TestShell
{

	private static $mysql;

	/* {{{ protected void setUp() */
	protected function setUp()
	{
		parent::setUp();

		\Myfox\Lib\Mysql::removeAllNames();
		\Myfox\Lib\Mysql::register('default', __DIR__ . '/ini/mysql.ini');
		self::$mysql    = \Myfox\Lib\Mysql::instance('default');
		self::$mysql->query(sprintf(
			"DELETE FROM %shost_list WHERE host_name LIKE 'unittest%%'",
			self::$mysql->option('prefix', '')
		));
	}
	/* }}} */

	/* {{{ public void test_should_server_get_option_works_fine() */
	public function test_should_server_get_option_works_fine()
	{
		try {
			Server::instance('unittest01')->option('');
			$this->assertTrue(false);
		} catch (\Exception $e) {
			$this->assertTrue($e instanceof \Myfox\Lib\Exception);
			$this->assertContains('Undefined mysql server named as "unittest01"', $e->getMessage());
		}

		$this->assertEquals('host_01_01', Server::instance('host_01_01')->option('host_name'));
		$this->assertEquals(Server::TYPE_VIRTUAL, Server::instance('host_01_02')->option('host_type'));
	}
	/* }}} */

	/* {{{ public void test_should_server_get_link_works_fine() */
	public function test_should_server_get_link_works_fine()
	{
		$ob	= Server::instance('host_01_01')->getlink();
		$this->assertTrue($ob instanceof \Myfox\Lib\Mysql);

		$my	= Server::instance('host_01_01')->getlink();
		$this->assertEquals($ob, $my);
	}
	/* }}} */

}

