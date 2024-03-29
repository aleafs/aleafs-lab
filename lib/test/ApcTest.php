<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | SessionTestShell													|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;
use \Aleafs\Lib\Cache\Apc;

require_once(__DIR__ . '/../class/TestShell.php');

class ApcTest extends LibTestShell
{

    private $shell  = 0;

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        Apc::cleanAllCache();
        parent::tearDown();
    }

    /* {{{ public void test_should_apc_without_compress_works_fine() */
    public function test_should_apc_without_compress_works_fine()
    {
        $val = array('a' => 'b', 'c' => array('d' => 'e'));
        $apc = new Apc(__METHOD__);
        $this->assertEquals(null, $apc->get('key1'), 'Apc should be empty.');

        $apc->set('key1', $val, 1);
        $apc->set('key2', $apc->get('key1'));

        $this->assertEquals($val, $apc->get('key1'), 'Apc set / get Error.');

        // XXX: 同一进程内apc不过期
        //$this->assertEquals(null, $apc->get('key1'), 'Apc should has been expired.');
        $this->assertEquals($val, $apc->get('key2'), 'Apc set / get Error.');

        $apc->delete('key1');
        $apc->delete('key2');
        $this->assertEquals(null, $apc->get('key2'), 'Apc should has been delete.');
    }
    /* }}} */

    /* {{{ public void test_should_apc_with_compress_works_fine() */
    public function test_should_apc_with_compress_works_fine()
    {
        $val = array('a' => 'b', 'c' => array('d' => 'e'));
        $apc = new Apc(__METHOD__, true);
        $this->assertEquals(null, $apc->get('key1'), 'Apc should be empty.');

        $apc->set('key1', $val, 1);
        $apc->set('key2', $apc->get('key1'));

        $this->assertEquals($val, $apc->get('key1'), 'Apc set / get Error with compress.');
    }
    /* }}} */

    /* {{{ public void test_should_cache_shell_works_fine() */
    public function test_should_cache_shell_works_fine()
    {
        $apc = new Apc(__METHOD__);
        $this->shell = 0;

        $this->assertEquals(md5(1), $apc->shell(array(&$this, 'loadShellData'), 1));
        $this->assertEquals(1, $this->shell);

        $this->assertEquals(md5(1), $apc->shell(array(&$this, 'loadShellData'), 1));
        $this->assertEquals(1, $this->shell);

        $this->assertEquals(md5(3), $apc->shell(array(&$this, 'loadShellData'), 3));
        $this->assertEquals(2, $this->shell);
    }
    /* }}} */

    /* {{{ public Mixture loadShellData() */
    public function loadShellData($key)
    {
        $this->shell++;
        return md5($key);
    }
    /* }}} */

}

