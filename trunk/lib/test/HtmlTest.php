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

    /* {{{ private static Boolean cleanDir() */
    /**
     * 递归地清理一个目录
     *
     * @access private static
     * @param  String $dir
     * @return Boolean true or false
     */
    private static function cleanDir($dir)
    {
        $d = dir($dir);
        if (!$d) {
            return false;
        }

        while (false !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $f = $dir . '/' . $entry;
            if (is_dir($f)) {
                if (!self::cleanDir($f)) {
                    return false;
                }
            } elseif (!@unlink($f)) {
                return false;
            }
        }
        $d->close();
        clearstatcache();

        return true;
    }
    /* }}} */

    protected function setUp()
    {
        parent::setUp();
        self::cleanDir(__DIR__ . '/html/obj');
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
        $html->assign('scalar', 'I\m a scalar variabe.');
        $html->assign('array', array(1, 2, 3));
        $html->render('index', 'user');

        $this->assertTrue(is_file(__DIR__ . '/html/obj/default/user/index.php'));
        $this->assertTrue(is_file(__DIR__ . '/html/obj/default/_element/footer.php'));
    }

}

