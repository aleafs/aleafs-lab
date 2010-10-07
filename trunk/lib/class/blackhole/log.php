<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 日志类	    														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: log.php 86 2010-06-01 03:52:51Z zhangxc83 $

namespace Aleafs\Lib\Blackhole;

class Log
{

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String $url
     * @return void
     */
    public function __construct()
    {
    }
    /* }}} */

    /* {{{ public void debug() */
    /**
     * 写Debug日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function debug($name, $data, $token = null)
    {
    }
    /* }}} */

    /* {{{ public void notice() */
    /**
     * 写Notice日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function notice($name, $data, $token = null)
    {
    }
    /* }}} */

    /* {{{ public void warn() */
    /**
     * 写Warn日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function warn($name, $data, $token = null)
    {
    }
    /* }}} */

    /* {{{ public void error() */
    /**
     * 写Error日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function error($name, $data, $token = null)
    {
    }
    /* }}} */

}

