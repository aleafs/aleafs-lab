<?php

use \Aleafs\Lib\Cache\Mcache;

require_once(__DIR__ . '/../class/TestShell.php');

class McacheTest extends \Aleafs\Lib\LibTestShell
{

    private $shell  = 0;

    protected function setUp()
    {
		parent::setUp();
		$this->shell	= 0;
    }

    protected function tearDown()
	{
		$this->cache->cleanAllCache();
        parent::tearDown();
	}

	private function initMemcache()
	{
		$this->cache	= new Mcache(array(
			'logurl'	=> '',
			'logtime'	=> true,
			'prefix'	=> 'test',
			'server'	=> array(
				'127.0.0.1:11211',
				'localhost:11211',
			),
		));
	}

	public function CallBack($key)
	{
		return md5(++$this->shell);
	}

	public function test_should_mcache_works_fine()
	{
		$this->initMemcache();

		$this->cache->setBufferWrite(false);

		$this->assertNull($this->cache->get('key1'));
		$this->assertTrue($this->cache->set('key1', 'val1'));
		$this->assertEquals('val1', $this->cache->get('key1'));

		$val = array(
			'text'	=> '我是中文',
		);
		$this->assertTrue($this->cache->set('key2', $val));
		$this->assertEquals($val, $this->cache->get('key2'));

		$this->assertTrue($this->cache->delete('key1'));
		$this->assertNull($this->cache->get('key1'));
	}

}
