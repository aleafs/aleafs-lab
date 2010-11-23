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

        $__dir  = dirname(__FILE__);
        Aleafs_Lib_Render_Html::init(array(
            'tpl_path'  => $__dir . '/../../../resource/themes',
            'obj_path'  => $__dir . '/../../../cache/themes',
            'theme'     => 'default',
        ));
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

}
