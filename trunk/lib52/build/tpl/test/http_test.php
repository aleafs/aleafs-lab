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
// $Id: http_test.php 54 2010-04-30 15:25:52Z zhangxc83 $

echo json_encode(array(
    'method'    => $_SERVER['REQUEST_METHOD'],
    'cookie'    => $_COOKIE,
    'getvar'    => $_GET,
    'postvar'   => $_POST,
));

