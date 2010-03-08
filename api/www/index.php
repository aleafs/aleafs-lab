<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Èë¿Ú³ÌÐò															|
// +--------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@gmail.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

define('APP_ROOT_DIR',  dirname(dirname(__FILE__)));

require_once(APP_ROOT_DIR . '/etc/define.etc.php');
require_once(HA_LIB_PATH . '/router.lib.php');
require_once(APP_ROOT_DIR . '/etc/server.etc.php');

HA_Router::instance()->dispatch();

