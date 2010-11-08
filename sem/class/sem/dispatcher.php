<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 请求转发器		    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: autoload.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

require_once(__DIR__ . '/../lib/dispatcher.php');

class Aleafs_Sem_Dispatcher extends Aleafs_Lib_Dispatcher
{

    /* {{{ public static void setAutoLoad() */
    /**
     * 自动加载初始化
     *
     * @access public static
     * @return void
     */
    public static function setAutoLoad()
    {
        require_once __DIR__ . '/../lib/autoload.php';

        Aleafs_Lib_AutoLoad::init();
        Aleafs_Lib_AutoLoad::register('aleafs_sem',     __DIR__);
    }
    /* }}} */

	/* {{{ private static string ctrl() */
	/**
	 * 获取控制器类名
	 *
	 * @access private static
	 * @return string
	 */
	private static function ctrl($ctrl)
	{
		return sprintf('Aleafs_Sem_Controller_%s', ucfirst(strtolower($ctrl)));
	}
	/* }}} */

}
