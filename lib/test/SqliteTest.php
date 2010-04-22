<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

namespace Aleafs\Lib;
use Aleafs\Lib\Db\Sqlite;

require_once(__DIR__ . '/../class/TestShell.php');

class SqliteTest extends LibTestShell
{
    private $dao;

    private $file;

    private $err;

    /* {{{ protected setUp() */
    protected function setUp()
    {
        parent::setUp();

        $this->err = error_reporting();
        error_reporting($this->err ^ E_WARNING);

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
        if (is_object($this->dao)) {
            $this->dao->query('DROP TABLE test_autoincrement_and_string');
        }
        if (is_file($this->file)) {
            @unlink($this->file);
            @rmdir(dirname($this->file));
        }
        $this->dao	= null;
        error_reporting($this->err);

        parent::tearDown();
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
        $data = array(
            array('sign' => 'ab', 'numb' => 1.00),
            array('sign' => 'ac', 'numb' => 0.01),
            array('sign' => 'ad', 'numb' => 1.00),
            array('sign' => 'ba', 'numb' => 1.00),
        );

        $dao = new Sqlite($this->file);     /* < 测文件存在情况下_connect分支 */
        $dao->table('test_autoincrement_and_string');
        foreach ($data AS $row) {
            $this->assertEquals(1, $dao->clear()->insert($row)->affectedRows(), 'Insert Error.');
        }

        $this->assertEquals(
            $dao->group('SUBSTR(sign,0,1)')
            ->order('k', 'DESC')
            ->select('SUBSTR(sign,0,1) AS k', 'COUNT(*) AS n', 'SUM(numb) AS s')
            ->getAll(),
                array(
                    array('k' => 'b', 'n' => 1, 's' => 1.00),
                    array('k' => 'a', 'n' => 3, 's' => 2.01),
                ),
                'Select Error When Group By SQL.'
            );
    }
    /* }}} */

    /* {{{ public test_should_transaction_works_fine() */
    public function test_should_transaction_works_fine()
    {
        $this->dao->begin();
        $this->dao->table('test_autoincrement_and_string');
        $this->assertEquals(
            $this->dao->clear()->insert(array('sign' => 'begin'))->affectedRows(),
            1, 'Affected Rows MUST BE 1 before commit.'
        );
        //TODO: 这里应该用另外一个连接来检查
        $this->dao->commit();
        $this->assertEquals(
            $this->dao->clear()->select('COUNT(*)')->getOne(),
            1, 'Rows Count must be 1 after commit.'
        );

        $this->dao->begin();
        $this->dao->begin();
        $this->dao->begin();
        $this->dao->clear()->delete();
        $this->dao->rollback();
        $this->dao->commit();         /**<  事务嵌套测试      */
        $this->dao->commit();
        $this->assertEquals(
            $this->dao->clear()->select('COUNT(*)')->getOne(),
            1, 'Rows Count must be 1 after rollback.'
        );
    }
    /* }}} */

    /* {{{ public test_should_error_case_works_fine() */
    public function test_should_error_case_works_fine()
    {
        $this->assertEquals(
            $this->dao->clear()->table('i_am_not_exists')->select('i_am_not_exists')->getAll(),
            null, 'Error SQL return More Data.'
        );

        $err = $this->dao->error();
        $this->assertTrue(
            isset($err['message']) && strlen($err['message']) > 0, 
            'Sqlite Error Doesn\'t Match.'
        );
    }
    /* }}} */

}
