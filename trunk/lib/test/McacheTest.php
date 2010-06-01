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
        unlink($this->logfile);

        parent::tearDown();
    }

    private function assertLogMatch($string, $error = null)
    {
        if (false === ($log = @file($this->logfile))) {
            $this->assertTrue(false, sprintf('Open log file "%s" failed.', $this->logfile));
            return;
        }

        $this->assertTrue((bool)preg_match(
            $string, end(array_filter(array_map('trim', $log)))
        ), $error);
    }

    /* {{{ public String shellCallBack() */
    public function shellCallBack($key)
    {
        $this->shell++;

        if (is_array($key)) {
            return array_combine($key, array_map('md5', $key));
        }

        return md5($key);
    }
    /* }}} */

    /* {{{ private void initMemcache() */
    private function initMemcache()
    {
        $this->cache    = null;
        $this->cache	= new Mcache(array(
            'logurl'	=> 'log://debug.notice.warn.error/' . $this->logfile . '?buffer=0',
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
        $this->assertLogMatch("/^NOTICE:\t\[.+?\]\t.+?MCACHE_GET_FAIL\t/");

        $this->assertTrue($this->cache->set('key1', 'val1'));
        $this->assertLogMatch("/^DEBUG:\t\[.+?\]\t.+?MCACHE_SET_OK\t/");

        $this->assertEquals('val1', $this->cache->get('key1'));
        $this->assertLogMatch("/^DEBUG:\t\[.+?\]\t.+?MCACHE_GET_OK\t/");

        $val = array(
            'text'	=> '我是中文',
        );
        $this->assertTrue($this->cache->set('key2', $val));
        $this->assertEquals($val, $this->cache->get('key2'));

        $this->assertTrue($this->cache->delete('key1'));
        $this->assertLogMatch("/^DEBUG:\t\[.+?\]\t.+?MCACHE_DEL_OK\t/");

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

        $val = array(
            'key1'  => md5('key1'),
            'key8'  => md5('key8'),
        );
        $this->assertEquals($val, $this->cache->shell(
            array(&$this, 'shellCallBack'),
            array('key1', 'key8')
        ));
        $this->assertEquals(2, $this->shell);
        $this->assertLogMatch("/^DEBUG:\t\[.+?\]\t.+?MCACHE_MULTI_SET_OK\t/");

        $obj = &$this;
        $this->assertEquals(md5('key8'), $this->cache->shell(
            function() use ($obj) {return $obj->shellCallBack('key8');},
            'key8'
        ));
        $this->assertEquals(2, $this->shell);
    }
    /* }}} */

}
