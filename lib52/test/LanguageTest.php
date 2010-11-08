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

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_LanguageTest extends Aleafs_Lib_LibTestShell
{

    protected function setUp()
    {
        parent::setUp();

        Aleafs_Lib_Language::cleanAllRules();
        Aleafs_Lib_Cache_Apc::cleanAllCache();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /* {{{ public void test_should_default_lang_works_fine() */
    public function test_should_default_lang_works_fine()
    {
        Aleafs_Lib_Language::init('zh_cn', true);
        Aleafs_Lib_Language::register('', __DIR__ . '/lang');

        $this->assertEquals(
            '我是UTF-8中文',
            Aleafs_Lib_Language::translate('i\'m a utf-8 chinese')
        );
        $debug = Aleafs_Lib_Language::debug('');

        // 以下验证cache
        $this->assertEquals(
            '我是UTF-8中文',
            Aleafs_Lib_Language::translate('i\'m a utf-8 chinese')
        );
        $this->assertEquals($debug, Aleafs_Lib_Language::debug(''));
        $this->assertEquals(
            $debug['mofile'],
            __DIR__ . '/lang/zh_CN.mo'
        );
    }
    /* }}} */

    /* {{{ public void test_should_multi_init_lang_works_fine() */
    public function test_should_multi_init_lang_works_fine()
    {
        Aleafs_Lib_Language::init('en_US', true);
        Aleafs_Lib_Language::register('', __DIR__ . '/lang');

        $this->assertEquals(
            'i\'m a utf-8 chinese',
            Aleafs_Lib_Language::translate('我是utf-8中文')
        );

        Aleafs_Lib_Language::init('zh_CN', false);
        $this->assertEquals(
            '我来自中文的zh_cn.mo',
            Aleafs_Lib_Language::translate('我是utf-8中文')
        );

        Aleafs_Lib_Language::unregister('');
        $this->assertEquals(
            '我是utf-8中文',
            Aleafs_Lib_Language::translate('我是utf-8中文')
        );

    }
    /* }}} */

    /* {{{ public void test_should_multi_lang_works_fine() */
    public function test_should_multi_lang_works_fine()
    {
        Aleafs_Lib_Language::init('zh_cn', true);
        Aleafs_Lib_Language::register('',      __DIR__ . '/lang');       /**<  zh_cn.mo      */
        Aleafs_Lib_Language::register('wAp',   __DIR__ . '/lang');       /**<  wap.zh_cn.mo      */
        Aleafs_Lib_Language::register('test',  __DIR__ . '/lang');       /**<  test.mo      */
        Aleafs_Lib_Language::register('file',  __DIR__ . '/lang/test_file.mo');
        Aleafs_Lib_Language::register('none',  __DIR__ . '/lang');       /**<  none.en_us.mo      */

        $this->assertEquals(
            '我是UTF-8中文',
            Aleafs_Lib_Language::translate('i\'m a utf-8 chinese', '')
        );

        $this->assertEquals(
            '(wap)我是UTF-8中文',
            Aleafs_Lib_Language::translate('i\'m a utf-8 chinese', 'wap')
        );

        $this->assertEquals(
            '(wap)我是UTF-8中文',
            Aleafs_Lib_Language::translate('i\'m a utf-8 chinese')
        );

        /**
         * @最后一个
         */
        $this->assertEquals(
            '我是文件 : test_file.mo',
            Aleafs_Lib_Language::translate('who are you')
        );

        $debug  = Aleafs_Lib_Language::debug();

        /**
         * @未找到的
         */
        $this->assertEquals(
            'File "none.en_us.mo" is mo file',
            Aleafs_Lib_Language::translate('File "none.en_us.mo" is mo file')
        );
    }
    /* }}} */

}

