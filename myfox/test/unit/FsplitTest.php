<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\Lib\Fsplit;

require_once(__DIR__ . '/../../lib/TestShell.php');

class FsplitTest extends \Myfox\Lib\TestShell
{

	/* {{{ protected void setUp() */
	protected function setUp()
	{
		parent::setUp();
		@exec(sprintf('rm -rf "%s"', __DIR__ . '/tmp'));
	}
	/* }}} */

	/* {{{ protected void tearDown() */
	protected function tearDown()
	{
		//@exec(sprintf('rm -rf "%s"', __DIR__ . '/tmp'));
		parent::tearDown();
	}
	/* }}} */

	/* {{{ private static String random() */
	private static function random($a, $b = 1000)
	{
		return implode('', array_fill(0, rand((int)$a, (int)$b), 'a'));
	}
	/* }}} */

    /* {{{ private static Integer fileline() */
    private static function fileline($fname)
    {
        $rt = exec(sprintf('wc -l "%s"', $fname), $output, $code);
        if (false === $rt || !empty($code)) {
            return false;
        }

        return (int)$rt;
    }
    /* }}} */

	/* {{{ private static Boolean prepare_test_file() */
	/**
	 * 准备测试文件
	 *
	 * @access private
	 * @return Boolean
	 */
	private static function prepare_test_file($fname, $lines = 2000)
	{
		$dr	= dirname($fname);
		if (!is_dir($dr) && !mkdir($dr, 0755, true)) {
			return false;
		}

		if (is_file($fname)) {
			@unlink($fname);
		}

		$rt	= array();
		for ($i = 0; $i < (int)$lines; $i++) {
			$rt[]	= self::random(100, 200);
			if (0 === ($i % 100)) {
				file_put_contents($fname, implode("\n", $rt) . "\n", FILE_APPEND, null);
				$rt	= array();
			}
		}

		if (!empty($rt)) {
			file_put_contents($fname, implode("\n", $rt) . "\n", FILE_APPEND, null);
		}

		return true;
	}
	/* }}} */

	/* {{{ public void test_should_file_split_by_line_works_fine() */
	public function test_should_file_split_by_line_works_fine()
	{
		$fname	= __DIR__ . '/tmp/fsplit_test.txt';
		$this->assertTrue(self::prepare_test_file($fname, 27000));
		$this->assertEquals(array(
			__DIR__ . '/tmp/fsplit_test.txt_0',
			__DIR__ . '/tmp/fsplit_test.txt_1',
			__DIR__ . '/tmp/fsplit_test.txt_2',
        ), Fsplit::chunk($fname, array(10000, 10000, 6000), __DIR__ . '/tmp'));

        $this->assertEquals(10000,  self::fileline(__DIR__ . '/tmp/fsplit_test.txt_0'));
        $this->assertEquals(10000,  self::fileline(__DIR__ . '/tmp/fsplit_test.txt_1'));
        $this->assertEquals(7000,   self::fileline(__DIR__ . '/tmp/fsplit_test.txt_2'));
	}
	/* }}} */

}

