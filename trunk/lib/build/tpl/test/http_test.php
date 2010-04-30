<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | http_test.php	        											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

echo json_encode(array(
    'method'    => $_SERVER['REQUEST_METHOD'],
    'cookie'    => $_COOKIE,
    'getvar'    => $_GET,
    'postvar'   => $_POST,
));

