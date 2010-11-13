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

require_once(dirname(__FILE__) . '/../class/sem/dispatcher.php');

Aleafs_Sem_Dispatcher::run(
    dirname(__FILE__) . '/../config/global.ini',
    isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
    file_get_contents('php://input')
);

