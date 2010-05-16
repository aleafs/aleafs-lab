<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | stream\mo.php	Mo文件解析											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib\Stream;

class Mo
{

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String $file
     * @return void
     */
    public function __construct($file)
    {
    }
    /* }}} */

    /* {{{ public Mixture gettext() */
    /**
     * 获取字符串翻译
     *
     * @access public
     * @param  String $string
     * @return String or Boolean false
     */
    public function gettext($string)
    {
        return false;
    }
    /* }}} */

}

