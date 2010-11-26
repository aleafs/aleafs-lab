<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | SOAP协议处理类	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: autoload.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Controller_Webui extends Aleafs_Lib_Controller
{

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /* }}} */

	/* {{{ protected void actionIndex() */
	/**
	 * 默认动作
	 *
	 * @access protected
	 * @return void
	 */
	protected function actionIndex($param, $post = null)
	{
		$render = new Aleafs_Lib_Render_Html();
		$render->assign('webroot', Aleafs_Lib_Context::get('webroot'));
		$render->render('index', 'webui', true);
	}
	/* }}} */

	/* {{{ protected void actionAbout() */
	/**
	 * 关于
	 *
	 * @access protected
	 * @return void
	 */
	protected function actionAbout($param, $post = null)
	{
		$render = new Aleafs_Lib_Render_Html();
		$render->assign('webroot', Aleafs_Lib_Context::get('webroot'));
		$render->render('about', 'webui', true);
	}
    /* }}} */

}
