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
        $render->assign('username', htmlspecialchars(Aleafs_Lib_Cookie::get('username')));
        $render->assign('appname',  htmlspecialchars(Aleafs_Lib_Cookie::get('appname')));

        if (!empty($param['msg'])) {
            $render->assign('message',  Aleafs_Sem_Account::getMessage($param['msg']));
        }

		$render->render(empty($param['tpl']) ? 'default' : $param['tpl'], 'login', true);
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
        $this->redirect(empty($param['url']) ? 'webui/index' : $param['url']);
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
        $un = isset($post['username']) ? $post['username'] : '';
        $pw = isset($post['password']) ? $post['password'] : '';
        $se = isset($post['_appname']) ? $post['_appname'] : '';

        $url    = Aleafs_Lib_Parser_Url::build('login', 'index', $param);
        if (empty($un) || empty($pw)) {
            $this->redirect($url);
            return;
        }

        $un = Aleafs_Sem_User::username($un, $se);
        if (false === ($un = Aleafs_Sem_Account::getUser($un, $pw))) {
            $this->redirect($url);
            return;
        }

        Aleafs_Lib_Session::set('isLogin',  true);
        Aleafs_Lib_Session::set('uinfo',    $un);

        Aleafs_Lib_Session::attr(Aleafs_Lib_Session::TS, time());
        Aleafs_Lib_Session::attr(Aleafs_Lib_Session::IP, Aleafs_Lib_Context::userip(true));

        Aleafs_Lib_Cookie::set('username',  $post['username']);
        Aleafs_Lib_Cookie::set('appname',   $post['appname']);

        $url    = Aleafs_Lib_Session::get('redirect');
        $this->redirect($url);
    }
    /* }}} */

}
