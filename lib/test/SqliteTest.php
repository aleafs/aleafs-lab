<?php
namespace Aleafs\Lib;

use Aleafs\Lib\Db\Sqlite;

require_once(__DIR__ . '/../class/autoload.php');
require_once 'PHPUnit/Framework/TestCase.php';

class SqliteTest extends \PHPUnit_Framework_TestCase
{

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
	}

}
