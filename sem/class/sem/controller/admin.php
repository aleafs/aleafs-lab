<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 后台管理控制器	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: admin.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Controller_Admin extends Aleafs_Lib_Controller
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
        Aleafs_Lib_Session::init('session');
    }
    /* }}} */

    /* {{{ public void execute() */
    /**
     * 执行请求
     *
     * @access public
     * @return void
     */
    public function execute($action, $param, $post = null)
    {
        $action	= strtolower(trim($action));
        if ('logout' != $action && !Aleafs_Sem_Account::isLogin()) {
            $this->redirect('account', 'login', $param);
            return;
        }

        return parent::execute($action, $param, $post);
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
        $render->assign('webroot',  Aleafs_Lib_Context::get('webroot'));
        $render->render('index', 'admin', true);
    }
    /* }}} */

}

