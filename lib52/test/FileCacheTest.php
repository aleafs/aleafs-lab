<?php

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_FileCacheTest extends Aleafs_Lib_LibTestShell
{

    private $shell  = 0;

    protected function setUp()
    {
		parent::setUp();
		$this->cache	= new Aleafs_Lib_Cache_File('test', __DIR__ . '/filecache');
		$this->shell	= 0;
    }

    protected function tearDown()
	{
		$this->cache->cleanAllCache();
        parent::tearDown();
	}

	public function CallBack($key)
	{
		$this->shell++;
		return md5($key);
	}

	public function test_should_file_cache_works_fine()
	{
		$this->assertEquals(null, $this->cache->get('i_am_not_exists'));
		$this->assertTrue($this->cache->set('key1', 'val1'));
		$this->assertEquals('val1', $this->cache->get('key1'));

		$val = array(
			'date'	=> '2010-5-31',
			'text'	=> '世界无烟日',
		);
		$this->assertTrue($this->cache->set('我是中文', $val));
		$this->assertEquals($val, $this->cache->get('我是中文'));

		// binary key
		$this->assertTrue($this->cache->set(md5('123', true), 'val2'));
		$this->assertEquals('val2', $this->cache->get(md5('123', true)));

		// delete
		$this->assertTrue($this->cache->delete('i_am_not_exists'));
		$this->assertTrue($this->cache->delete('key1'));
		$this->assertEquals(null, $this->cache->get('key1'));
	}

	public function test_should_exception_throw_out_when_write_error()
	{
		$cache	= new Aleafs_Lib_Cache_File('readonly', __DIR__ . '/filecache');
		$cache->cleanAllCache();

		@mkdir(__DIR__ . '/filecache/readonly', 0444, true);

		$error	= error_reporting();
		error_reporting($error - E_WARNING);
		try {
			$cache->set('key1', 'val1');
			$this->assertTrue(false);
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Aleafs_Lib_Exception);
			$this->assertContains(
				'Derectory "' . __DIR__ . '/filecache/readonly/',
				$e->getMessage()
			);
		}

		@chmod(__DIR__ . '/filecache/readonly', 0744);
		error_reporting($error);
	}

	public function test_should_expire_works_fine()
	{
		$this->cache->set('key1', 'val1', 1);
		$this->assertEquals('val1', $this->cache->get('key1'));
		sleep(2);
		$this->assertEquals(null, $this->cache->get('key1'));
	}

	public function test_should_cache_shell_works_fine()
	{
		$this->assertEquals(
			md5('shell'),
			$this->cache->shell(
				array(&$this, 'CallBack'),
				'shell',
				1
			)
		);
		$this->assertEquals(1, $this->shell);

		$obj = &$this;
		$this->assertEquals(
			md5('shell'),
			$this->cache->shell(
				function() use ($obj) {return $obj->CallBack('shell');},
				'shell',
				1
			)
		);
		$this->assertEquals(1, $this->shell);
	}

	public function _test_should_binary_value_works_fine()
	{
		$val = array(
			'char'	=> '我是中文',
			'data'	=> md5('我是中文', true),
		);
		$this->assertTrue($this->cache->set(md5('213', true), $val));
		$this->assertEquals($val, $this->cache->get(md5('213', true)));
	}

}
