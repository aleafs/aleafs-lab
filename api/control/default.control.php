<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 默认控制器															|
// +--------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@gmail.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Control_Abstract')) {
    exit('Access Denied!');
}

class App_Control_Default extends HA_Control_Abstract
{

    /* {{{ protected Boolean _actionIndex() */
    /**
     * 默认action
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _actionIndex()
    {
        return '<!--STATUS OK-->';
    }
    /* }}} */

}

