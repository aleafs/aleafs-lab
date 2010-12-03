<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 用户登录控制器	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: autoload.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Controller_Login extends Aleafs_Lib_Controller
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
	 * 默认动作(登录页)
	 *
	 * @access protected
	 * @return void
	 */
	protected function actionIndex($param, $post = null)
	{
		$render = new Aleafs_Lib_Render_Html();
		$render->assign('webroot',  Aleafs_Lib_Context::get('webroot'));
        $render->assign('title',    '用户登录');

        if (!empty($param['url'])) {
            $render->assign('redirect', $param['url']);
        }

        if (!empty($param['msg'])) {
            $render->assign('message',  $param['msg']);
        }
		$render->render('index', 'login', true);
	}
	/* }}} */

    /* {{{ protected void actionLogout() */
    /**
     * 退出登录
     *
     * @access protected
     * @return void
     */
    protected function actionLogout($param, $post = null)
    {
        Aleafs_Lib_Session::destroy();
        if (empty($param['url'])) {
            $this->redirect('webui', 'index');
        } else {
            // XXX: 死循环风险
            header(sprintf('Location: %s', $param['url']));
        }
    }
    /* }}} */

    /* {{{ protected void actionPublic() */
    /**
     * 登录验证
     *
     * @access protected
     * @return void
     */
    protected function actionPublic($param, $post = null)
    {
    }
    /* }}} */

}
