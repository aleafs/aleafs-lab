<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 统一入口程序		 					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.com. All Rights Reserved	        			|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//

if (!defined('__DIR__')) {
    define('__DIR__',   dirname(__FILE__));
}

require_once(__DIR__ . '/../class/sem/dispatcher.php');

Aleafs_Sem_Dispatcher::run(
    __DIR__ . '/../config/global.ini',
    isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
    file_get_contents('php://input')
);

