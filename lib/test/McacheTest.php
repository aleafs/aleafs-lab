<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Aleafs\Lib\Cache\Mcache;

require_once(__DIR__ . '/../class/TestShell.php');

class McacheTest extends \Aleafs\Lib\LibTestShell
{

    private $shell  = 0;

    protected function setUp()
    {
        parent::setUp();

        $this->shell	= 0;
        $this->logfile	= __DIR__ . '/logs/mcache_test.log';
    }

    protected function tearDown()
    {
        $this->cache->cleanAllCache();
        @unlink($this->logfile);

        parent::tearDown();
    }

    public function shellCallBack($key)
    {
        $this->shell++;

        if (is_array($key)) {
            return array_combine($key, array_map('md5', $key));
        }

        return md5($key);
    }

    /* {{{ private void initMemcache() */
    private function initMemcache()
    {
        $this->cache    = null;
        $this->cache	= new Mcache(array(
            'logurl'	=> 'log://debug.notice.warn.error/' . $this->logfile,
            'logtime'	=> true,
            'prefix'	=> 'test',
            'server'	=> array(
                '127.0.0.1:11211',
                'localhost:11211',
            ),
        ));
    }
    /* }}} */

    /* {{{ public void test_should_mcache_works_fine() */
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
    /* }}} */

    /* {{{ public void test_should_mcache_buffer_write_works_fine() */
    public function test_should_mcache_buffer_write_works_fine()
    {
        $this->initMemcache();

        $this->cache->setBufferWrite(true);

        $this->assertNull($this->cache->get('key1'));
        $this->assertTrue($this->cache->set('key1', 'val1'));

        $this->cache	= null;
        $this->initMemcache();

        $this->assertEquals('val1', $this->cache->get('key1'));

        $val = array(
            'text'	=> '我是中文',
        );
        $this->assertTrue($this->cache->set('key2', $val));


        $this->cache	= null;
        $this->initMemcache();
        $this->assertEquals($val, $this->cache->get('key2'));

        $this->assertTrue($this->cache->delete('key1'));

        $this->cache	= null;
        $this->initMemcache();
        $this->assertNull($this->cache->get('key1'));
    }
    /* }}} */

    /* {{{ public void test_should_cache_shell_works_fine() */
    public function test_should_cache_shell_works_fine()
    {
        $this->initMemcache();
        $this->cache->setBufferWrite(false);

        $this->assertEquals(md5('key1'), $this->cache->shell(
            array(&$this, 'shellCallBack'),
            'key1'
        ));
        $this->assertEquals(1, $this->shell);

    }
    /* }}} */

}
