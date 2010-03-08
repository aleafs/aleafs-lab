<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Ä¬ÈÏ¿ØÖÆÆ÷															|
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
     * Ä¬ÈÏaction
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

