<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 入口程序															|
// +--------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@gmail.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

define('APP_ROOT_DIR',  dirname(dirname(__FILE__)));

require_once(APP_ROOT_DIR . '/etc/global.etc.php');

require_once(HA_LIB_PATH . '/router.lib.php');

HA_Router::instance()->dispatch();

