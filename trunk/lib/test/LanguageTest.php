<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | LanguageTest.php	    											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: ..\test\LanguageTest.php 2010-05-22 星期六  aleafs Exp $

use \Aleafs\Lib\Cache\Apc;
use \Aleafs\Lib\Language;
use \Aleafs\Lib\LibTestShell;

require_once(__DIR__ . '/../class/TestShell.php');

class LanguageTest extends LibTestShell
{

    protected function setUp()
    {
        parent::setUp();

        /**
         * 清理掉所有的语言包
         */
        Language::unregister(null);
        Apc::cleanAllCache();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function test_should_default_lang_works_fine()
    {
        Language::init('zh_cn', true);
        Language::register('', __DIR__ . '/lang');

        $this->assertEquals(
            '我是UTF-8中文',
            Language::translate('i\'m a utf-8 chinese')
        );
        $debug = Language::debug('');

        // 以下验证cache
        $this->assertEquals(
            '我是UTF-8中文',
            Language::translate('i\'m a utf-8 chinese')
        );
        $this->assertEquals($debug, Language::debug(''));
    }

    public function _test_should_multi_lang_works_fine()
    {
        Language::init('zh_cn', true);
        Language::register('',      __DIR__ . '/lang');
        Language::register('wap',   __DIR__ . '/lang');

        $this->assertEquals(
            '我是UTF-8中文',
            Language::translate('i\'m a utf-8 chinese', '')
        );

        $this->assertEquals(
            '(wap)我是UTF-8中文',
            Language::translate('i\'m a utf-8 chinese', 'wap')
        );

        $this->assertEquals(
            '(wap)我是UTF-8中文',
            Language::translate('i\'m a utf-8 chinese')
        );

        $debug  = Language::debug();
    }

}

