<?php

use \Aleafs\Lib\Cache;

require_once(__DIR__ . '/../class/TestShell.php');

class CacheTest extends \Aleafs\Lib\LibTestShell
{

    protected function setUp()
    {
		parent::setUp();
    }

    protected function tearDown()
	{
        parent::tearDown();
	}

	/* {{{ public void test_should_cache_instance_works_fine() */
	public function test_should_cache_instance_works_fine()
	{
		// xxx : APC
		$cache	= Cache::instance('unittest', Cache::APC | Cache::FILE);
		$this->assertTrue($cache->set('key1', 'val1', 10));
		$this->assertEquals('val1', $cache->get('key1'));

		$cache->dversion('v1');
		$this->assertEquals(null, $cache->get('key1'));
		$this->assertTrue($cache->add('key1', 'val2', 10));
		$this->assertEquals('val2', $cache->get('key1'));

		$cache->delete('key1');
		$this->assertEquals(null, $cache->get('key1'));
	}
	/* }}} */

}

