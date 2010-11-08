<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | UrlTest.php							    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2003 - 2010 Taobao.com. All Rights Reserved				|
// +------------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_UrlTest extends Aleafs_Lib_LibTestShell
{

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_parse_right_module_and_action_and_param() */
    public function test_should_parse_right_module_and_action_and_param()
    {
        $url = new Aleafs_Lib_Parser_Url('');
        $this->assertEquals('', $url->module);
        $this->assertEquals('', $url->action);
        $this->assertEquals(array(), $url->param);

        $url = new Aleafs_Lib_Parser_Url('test');
        $this->assertEquals('test', $url->module);
        $this->assertEquals('', $url->action);
        $this->assertEquals(array(), $url->param);

        $url = new Aleafs_Lib_Parser_Url('test//index/a/b/c/2');
        $this->assertEquals('test', $url->module);
        $this->assertEquals('index', $url->action);
        $this->assertEquals(array('a' => 'b', 'c' => '2'), $url->param);

        $url = new Aleafs_Lib_Parser_Url("test\t/in\ndex/__SQL__/b/c/2/d");
        $this->assertEquals('test', $url->module);
        $this->assertEquals('index', $url->action);
        $this->assertEquals(array(
            '__SQL__' => 'b', 'c' => '2', 'd' => true
        ), $url->param);
    }
    /* }}} */

    /* {{{ public void test_should_build_right_url_ok() */
    public function test_should_build_right_url_ok()
    {
        $this->assertEquals(
            'module/action/name/' . urlencode('朱镕基') . '/age/2',
            Aleafs_Lib_Parser_Url::build('module', 'action', array(
                'na me' => '朱镕基',
                'age  ' => 2,
                'array' => array('我应该被忽略'),
            ))
        );
    }
    /* }}} */

    /* {{{ public void test_should_parse_zero_correctly() */
    /**
     * THIS IS A BUG CASE
     */
    public function test_should_parse_zero_correctly()
    {
        $url = new Aleafs_Lib_Parser_Url('test//index/a/b/c/0');
        $this->assertEquals(array('a' => 'b', 'c' => '0'), $url->param);
    }
    /* }}} */

}

