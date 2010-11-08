<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | DebugPoolTest.php								    				|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: DebugPoolTest.php 2010-06-01 aleafs Exp $

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_DebugCoverageTest extends Aleafs_Lib_LibTestShell
{

    private $dbfile;

    protected function setUp()
    {
        parent::setUp();
        $this->dbfile   = __DIR__ . '/logs/coverage.db';
    }

    protected function tearDown()
    {
        @unlink($this->dbfile);
        parent::tearDown();
    }

    public function test_should_coverage_works_fine()
    {
        Aleafs_Lib_Debug_Coverage::init($this->dbfile);

        foreach (range(0, 12) AS $num) {
            $num = pow($num, 2);
        }

        Aleafs_Lib_Debug_Coverage::flush();
        $this->assertTrue(is_file($this->dbfile));
    }

}

