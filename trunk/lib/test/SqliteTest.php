<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

namespace Aleafs\Lib;
use Aleafs\Lib\Db\Sqlite;

require_once(__DIR__ . '/../class/TestShell.php');

class SqliteTest extends LibTestShell
{
	private $dao;

	private $file;

    /* {{{ protected setUp() */
	protected function setUp()
	{
		parent::setUp();

		$this->file = __DIR__ . '/dbtest/i_am_only_for_test.sqlite';
		$this->dao	= new Sqlite($this->file);
		$this->assertTrue((bool)$this->dao->query(
			'CREATE TABLE test_autoincrement_and_string (
				seqid INTEGER AUTOINCREMENT,
				sign char(32) NOT NULL DEFAULT "",
				numb NUMERIC NOT NULL DEFAULT "0.00",
				PRIMARY KEY (seqid),
				UNIQUE (sign)
			)'
		), 'Sqlite Create Table Error!');
    }
    /* }}} */

    /* {{{ protected tearDown() */
	protected function tearDown()
	{
		parent::tearDown();

		if (is_file($this->file)) {
			unlink($this->file) && rmdir(dirname($this->file));
		}
		$this->dao	= null;
    }
    /* }}} */

	/* {{{ public test_should_insert_update_delete_select_ok() */
	public function test_should_insert_update_delete_select_ok()
	{
		$data = array(
			array('sign' => md5('123456'), 'numb' => 2.999999999999),
			array('sign' => 'I\'am a test', 'numb' => -2.999999999999),
			array('sign' => 'here iS me', 'numb' => 2.999999999999),
			array('sign' => 'heer is me', 'numb' => 2.999999999998),
			array('sign' => 'ｕｔｆ８字符', 'numb' => 2.999999999998),
			array('sign' => iconv('utf-8', 'gbk//IGNORE', 'ｕｔｆ８字符'), 'numb' => 2.999999999998),
		);

		$this->dao->table('test_autoincrement_and_string');
		foreach ($data AS $key => $row) {
			$this->assertEquals(1, $this->dao->clear()->insert($row)->affectedRows(), 'Sqlite Insert Data Error.');
			$this->assertEquals($key + 1, $this->dao->lastId(), 'Sqlite Last Insert Id Error.');
		}

		$this->assertEquals(
			$this->dao->clear()->order('seqid', 'ASC')
			->where('seqid', -1, Database::NE, false)
			->where('seqid', array(234234234,12131321), Database::NOTIN, false)
			->where('seqid', count($data) + 1000, Database::LT, false)
			->where('seqid', -1, Database::GT)
			->where('seqid', 'sadfkwe', Database::NOTLIKE)
			->select('sign', 'numb')->getAll(),
			$data, 'getAll Data Doesn\'t match after insert.'
		);

		$this->assertEquals(
			$this->dao->clear()->where('seqid', 3, Database::LE)->update(array('numb' => 'round(numb, 2)'), array('numb' => false))->affectedRows(),
			3, 'Affected Rows is not 3 when update.'
		);

		$this->assertEquals(
			$this->dao->clear()->select(array('numb'))->getAll(),
			array(
				array('numb' => 3.00),
				array('numb' => -3.00),
				array('numb' => '3.00'),
				array('numb' => '2.999999999998'),
				array('numb' => '2.999999999998'),
				array('numb' => '2.999999999998'),
			),
			'Data Doesn\'t match after update.'
		);

		$this->assertEquals(
			$this->dao->clear()->where('seqid', array(1,4,99999), Database::IN, false)->delete()->affectedRows(),
			2,
			'Delete affected Rows Error.'
		);

		$this->assertEquals(
			$this->dao->clear()->select('COUNT(*)')->getOne(),
			count($data) - 2,
			'Data Rows Doesn\'t match after delete'
		);
	}
	/* }}} */

    /* {{{ public test_should_select_ok_when_use_group_by() */
    public function test_should_select_ok_when_use_group_by()
    {
        $dao = new Sqlite($this->file);     /* < 测文件存在情况下_connect分支 */
    }
    /* }}} */

    /* {{{ public test_should_transaction_works_fine() */
    public function test_should_transaction_works_fine()
    {
    }
    /* }}} */

    /* {{{ public test_should_error_case_works_fine() */
    public function test_should_error_case_works_fine()
    {
    }
    /* }}} */

}
