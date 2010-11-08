<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | stream\text.php	    											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: text.php 2010-06-02 aleafs Exp $

class Aleafs_Lib_Stream_Text
{

    /* {{{ 静态常量 */

    /**
     * @读取缓存
     */
    const READ_BUFFER   = 10485760;       /**< 10M       */

    /**
     * @读取返回码
     */
    const READ_ERROR = 0;          /**<  出错      */
    const READ_MORE  = 1;          /**<  继续      */
    const READ_EOF   = 2;          /**<  完成      */

    /* }}} */

}

