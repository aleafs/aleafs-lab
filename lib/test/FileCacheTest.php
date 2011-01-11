<?php

use \Aleafs\Lib\Cache\File;

require_once(__DIR__ . '/../class/TestShell.php');

class FileCacheTest extends \Aleafs\Lib\LibTestShell
{

    private $shell  = 0;

    protected function setUp()
    {
		parent::setUp();
		$this->cache	= new File('filecache', __DIR__ . '/temp');
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
		$this->assertFalse($this->cache->add(md5('123', true), '肯定添加不进'));

		// delete
		$this->assertFalse($this->cache->delete('i_am_not_exists'));
		$this->assertTrue($this->cache->delete('key1'));
		$this->assertEquals(null, $this->cache->get('key1'));
		$this->assertTrue($this->cache->add('key1', '先删除后添加成功'));
	}

	public function test_should_expire_works_fine()
	{
		$this->cache->set('key1', 'val1', 1);
		$this->assertEquals('val1', $this->cache->get('key1'));
		sleep(2);
		$this->assertEquals(null, $this->cache->get('key1', true));
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

	// xxx: 混合值还不行
	public function test_should_binary_value_works_fine()
	{
		$val = array(
			'char'	=> '我是中文',
			//'data'	=> md5('我是中文', true),
		);
		$this->assertTrue($this->cache->set('binary_key', $val));
		$this->assertEquals($val, $this->cache->get('binary_key'));
	}

}
