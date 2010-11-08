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

class Aleafs_Sem_Dispatcher extends \Aleafs\Lib\Dispatcher
{

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
