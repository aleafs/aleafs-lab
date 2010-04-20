<?php
namespace Aleafs\Lib;

use Aleafs\Lib\Db\Sqlite;

require_once(__DIR__ . '/../class/TestShell.php');

class SqliteTest extends LibTestShell
{
	private $dao;

	protected function setUp()
	{
		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function test_should_open_file_when_there_is_no_file()
	{
		$dao = new Sqlite('./there_is_no_file.sqlite');
	}

}
