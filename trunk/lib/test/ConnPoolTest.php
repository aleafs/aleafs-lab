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
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /* {{{ public function test_should_right_select_by_random() */
    /**
     * @return  
     */
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

        $pool   = new ConnPool(__METHOD__);
        foreach ($hosts AS $host => $weight) {
            $pool->register($host, $weight);
        }
        $pool->register('I\m not exists', 1000);

        $result = array();
        for ($i = 0; $i < 10000; $i++) {
            $host = $pool->getHost();
            if (!preg_match('/^[\d\.]+:\d+$/is', $host)) {         /**<  模拟连接      */
                $pool->setOffline();
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
        $pool = new ConnPool(__METHOD__);
        $pool->register('localhost', 1)
            ->register('www.baidu.com', 1)
            ->register('www.google.com', 1)
            ->unregister('localhost')
            ->setOffline('www.baidu.com')
            ->setOffline('www.google.com');

        try {
            $pool->getHost();
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

}

