<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Unit Test Case Class												|
// +--------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: TestShell.php 47 2010-04-26 05:27:46Z zhangxc83 $

namespace Myfox\Lib;

require_once(__DIR__ . '/autoload.php');
require_once('PHPUnit/Framework/TestCase.php');

date_default_timezone_set('Asia/Shanghai');

class TestShell extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
        \Myfox\Lib\AutoLoad::init();
        \Myfox\Lib\AutoLoad::register('myfox\\app',    __DIR__ . '/../app');

        \Myfox\Lib\Cache\Apc::cleanAllCache();
    }

    protected static function getLogContents($file, $offs = -1)
    {
        $data   = array_filter(array_map('trim', (array)@file($file)));
        $lines  = count($data);
        $offs   = $offs < 0 ? $lines + $offs : $offs;
        $offs   = max(0, $offs);

        return $offs >= $lines ? end($data) : $data[$offs];
    }

}

