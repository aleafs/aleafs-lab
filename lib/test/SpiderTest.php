<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | DecareTest.php							    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2003 - 2010 Taobao.com. All Rights Reserved				|
// +------------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+

use \Edp\Core\Factory;
use \Edp\Myfox\MyfoxUnitTestShell;
use \Edp\Myfox\Spider;

require_once(__DIR__ . '/../../classes/myfox/MyfoxUnitTestShell.php');

class SpiderTest extends MyfoxUnitTestShell
{

    private $test_script;

    private $logfile;

    private $logurl;

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();

        $ini    = &Factory::getIni(__DIR__ . '/ini/myfox.ini')->log_url;
        $ini    = parse_url($ini);
        $ini['path'] = dirname($ini['path']) . '/spider-test.log';

        $this->logfile  = $ini['path'];
        $this->logurl   = sprintf('log://%s%s?%s', $ini['host'], $ini['path'], $ini['query']);

        $this->test_script  = sprintf(
            '%s/myfox_test/test_spider_method_and_data.php',
            Factory::getIni(__DIR__ . '/ini/myfox.ini')->run_user
        );
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        @unlink($this->logfile);
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_spider_method_url_data_works_fine() */
    public function test_should_spider_method_url_data_works_fine()
    {
        $spider = new Spider(array('log_url' => $this->logurl));
        $spider->cleanAllData();

        $url    = 'http://127.0.0.1/' . $this->test_script;
        $spider->register($url);
        $result = json_decode($spider->getResult($url), true);
        $this->assertEquals($this->test_script, ltrim($result['url'], '/'));
        $this->assertEquals('GET', $result['method']);
        $this->assertEquals('', $result['data']);
        $this->assertEquals('127.0.0.1', $result['host']);
        $this->assertContains(
            "\tINFO\tSPIDER_OK\t-\t{\"url\":\"http:\/\/127.0.0.1\/",
            self::getLogContent($this->logfile, -1)
        );

        $time1  = $result['time'];

        $spider->cleanAllData();

        $url    = 'http://127.0.0.1/' . $this->test_script;
        $spider->register($url, 'POST', array('a' => 'cc', 'b' => '测试中文'));
        $spider->register('http://localhost/' . $this->test_script, 'PUT');
        $spider->register('http://i_am_not_exists');

        $result = json_decode($spider->getResult($url), true);
        $this->assertEquals($this->test_script, ltrim($result['url'], '/'));
        $this->assertEquals('POST', $result['method']);
        $this->assertEquals('a=cc&b=' . urlencode('测试中文'), $result['data']);
        $this->assertEquals('127.0.0.1', $result['host']);
        $this->assertContains(
            "\tWARNING\tSPIDER_ERROR\t-\t{\"url\":\"http:\/\/i_am_not_exists\",\"code\"",
            file_get_contents($this->logfile)
        );

        $time2  = $result['time'];
        $this->assertTrue($time2 - $time1 >= 1);

        $result = json_decode($spider->getResult('http://localhost/' . $this->test_script), true);
        $this->assertEquals('PUT', $result['method']);
        $this->assertEquals('localhost', $result['host']);

        // 并发
        $time3  = $result['time'];
        $this->assertTrue($time3 - $time2 < 1);
    }
    /* }}} */

    /* {{{ public void test_should_max_threads_control_works_fine() */
    public function test_should_max_threads_control_works_fine()
    {
        $spider = new Spider();
        $spider->cleanAllData();

        $urls   = array();
        for ($i = 0; $i < 12; $i++) {
            $url    = sprintf(
                'http://%s/%s?i=%d',
                $i > 9 ? 'localhost' : '127.0.0.1',
                $this->test_script, $i
            );
            $spider->register($url);
            $urls[] = $url;
        }

        $result = array();
        foreach ($urls AS $url) {
            $rs = json_decode($spider->getResult($url), true);
            $result[] = $rs['time'];
        }

        $this->assertTrue($result[9] - $result[0] < 1);
        $this->assertTrue($result[10] - $result[0] >= 1);
        $this->assertTrue($result[11] - $result[10] < 1);
    }
    /* }}} */

}

