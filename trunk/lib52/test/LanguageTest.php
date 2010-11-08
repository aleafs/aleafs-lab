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

        Language::cleanAllRules();
        Apc::cleanAllCache();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /* {{{ public void test_should_default_lang_works_fine() */
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
        $this->assertEquals(
            $debug['mofile'],
            __DIR__ . '/lang/zh_CN.mo'
        );
    }
    /* }}} */

    /* {{{ public void test_should_multi_init_lang_works_fine() */
    public function test_should_multi_init_lang_works_fine()
    {
        Language::init('en_US', true);
        Language::register('', __DIR__ . '/lang');

        $this->assertEquals(
            'i\'m a utf-8 chinese',
            Language::translate('我是utf-8中文')
        );

        Language::init('zh_CN', false);
        $this->assertEquals(
            '我来自中文的zh_cn.mo',
            Language::translate('我是utf-8中文')
        );

        Language::unregister('');
        $this->assertEquals(
            '我是utf-8中文',
            Language::translate('我是utf-8中文')
        );

    }
    /* }}} */

    /* {{{ public void test_should_multi_lang_works_fine() */
    public function test_should_multi_lang_works_fine()
    {
        Language::init('zh_cn', true);
        Language::register('',      __DIR__ . '/lang');       /**<  zh_cn.mo      */
        Language::register('wAp',   __DIR__ . '/lang');       /**<  wap.zh_cn.mo      */
        Language::register('test',  __DIR__ . '/lang');       /**<  test.mo      */
        Language::register('file',  __DIR__ . '/lang/test_file.mo');
        Language::register('none',  __DIR__ . '/lang');       /**<  none.en_us.mo      */

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

        /**
         * @最后一个
         */
        $this->assertEquals(
            '我是文件 : test_file.mo',
            Language::translate('who are you')
        );

        $debug  = Language::debug();

        /**
         * @未找到的
         */
        $this->assertEquals(
            'File "none.en_us.mo" is mo file',
            Language::translate('File "none.en_us.mo" is mo file')
        );
    }
    /* }}} */

}

