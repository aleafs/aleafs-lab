<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | ConnPoolTest.php	    											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;
require_once(__DIR__ . '/../class/TestShell.php');

class ConnPoolTest extends LibTestShell
{

    protected function setUp()
    {
        parent::setUp();
        $this->pool = new ConnPool(__CLASS__);
    }

    protected function tearDown()
    {
        if (!empty($this->pool)) {
            $this->pool->cleanAll();
            unset($this->pool);
        }

        parent::tearDown();
    }

    /* {{{ public function test_should_right_select_by_random() */
    public function test_should_random_select_works_fine()
    {
        $hosts  = array(
            '127.0.0.1:1234' => 1,
            '127.0.0.1:1235' => 1,
            '127.0.0.1:1236' => 3,
            '127.0.0.1:1237' => 2,
            '127.0.0.1:1238' => 2,
            '127.0.0.1:1239' => 1,
        );

        foreach ($hosts AS $host => $weight) {
            $this->pool->register($host, $weight);
        }
        $this->pool->register('I\m not exists', 1000);

        $result = array();
        for ($i = 0; $i < 10000; $i++) {
            $host = $this->pool->getHost();
            if (!preg_match('/^[\d\.]+:\d+$/is', $host)) {         /**<  模拟连接      */
                $this->pool->setOffline();
            }
            $result[$host]++;
        }
        $this->assertTrue($result['I\m not exists'] < 5, 'setOffline Doesn\t work.');

        $total  = 10000 / array_sum(array_values($hosts));
        foreach ($hosts AS $host => $weight) {
            $this->assertTrue(
                ($result[$host] >= 0.85 * $weight * $total) && ($result[$host] <= 1.15 * $weight * $total),
                sprintf('Host "%s" random selector error.', $host)
            );
        }

    }
    /* }}} */

    /* {{{ public function test_should_throw_exception_when_all_offline() */
    public function test_should_throw_exception_when_all_offline()
    {
        $this->pool->register('localhost', 1)
            ->register('www.baidu.com', 1)
            ->register('www.google.com', 1)
            ->unregister('localhost')
            ->setOffline('www.baidu.com')
            ->setOffline('www.google.com');

        try {
            $this->pool->getHost();
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Aleafs\Lib\Exception);
            $this->assertContains(
                'There is no available server',
                $e->getMessage(),
                'Exception Message Error.'
            );
        }
    }
    /* }}} */

    /* {{{ public function test_should_live_time_works_fine() */
    public function test_should_live_time_works_fine()
    {
        $pool = new ConnPool(__CLASS__, 2);
        if (!$pool->useCache()) {
            return;
        }

        $pool->register('www.baidu.com', 1)->setOffline('www.baidu.com');
        unset($pool);

        $pool = new ConnPool(__CLASS__);
        $pool->register('www.baidu.com', 1)->register('www.google.com', 1);

        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue(
                $pool->getHost() != 'www.baidu.com',
                'Offline host should NOT appear!'
            );
        }
        unset($pool);

        sleep(2);
        $pool = new ConnPool(__CLASS__);
        $pool->register('www.baidu.com', 1)->register('www.google.com', 1);

        $return = array();
        for ($i = 0; $i < 1000; $i++) {
            $return[$pool->getHost()]++;
        }

        $this->assertTrue(
            485 <= $return['www.baidu.com'] && 
            $return['www.baidu.com'] <= 515
        );
        $this->assertTrue(
            485 <= $return['www.google.com'] &&
            $return['www.google.com'] <= 515
        );
    }
    /* }}} */

}
