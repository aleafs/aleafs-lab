<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | HtmlTest.php	        											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

use \Aleafs\Lib\Render\Html;

require_once(__DIR__ . '/../class/TestShell.php');

class HtmlTest extends LibTestShell
{

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function test_should_html_compile_works_fine()
    {
        Html::init(array(
            'tpl_path'  => __DIR__ . '/html/tpl',
            'obj_path'  => __DIR__ . '/html/obj',
            'theme'     => 'default',
            'expire'    => 0,
        ));

        $html   = new Html();
        $html->assign('scalar', 'I\m a scalar variabe.')
            ->assign('array', array(1, 2, 3));
        $html->render('index', 'default');
    }

}

