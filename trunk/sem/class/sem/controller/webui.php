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
		$render->assign('webroot',  Aleafs_Lib_Context::get('webroot'));
        $render->assign('title',    '网络推广好助手');
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
        $render->assign('webroot',  Aleafs_Lib_Context::get('webroot'));
        $render->assign('title',    '关于');
		$render->render('about', 'webui', true);
	}
    /* }}} */

	/* {{{ protected void actionFaq() */
	/**
	 * 常见问题
	 *
	 * @access protected
	 * @return void
	 */
	protected function actionFaq($param, $post = null)
	{
		$render = new Aleafs_Lib_Render_Html();
        $render->assign('webroot',  Aleafs_Lib_Context::get('webroot'));
        $render->assign('title',    '常见问题');
		$render->render('faq', 'webui', true);
	}
    /* }}} */

	/* {{{ protected void actionDownload() */
	/**
	 * 软件下载
	 *
	 * @access protected
	 * @return void
	 */
    protected function actionDownload($param, $post = null)
    {
        if (empty($param['machine'])) {
            throw new Aleafs_Lib_Exception('Machine type is required for download.');
        }

        $config = Aleafs_Lib_Configer::instance('default');
        $file   = sprintf(
            $config->get('software.source'),
            strtolower(trim($param['machine']))
        );

        if (!is_readable($file)) {
            throw new Aleafs_Lib_Exception(sprintf('No such file named as "%s"', $file));
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        readfile($file);
        flush();

        $mysql  = new Aleafs_Lib_Db_Mysql('mysql');
        $mysql->clear();

        $time   = date('Y-m-d H:i:s');
        $query  = sprintf(
            "INSERT INTO soft_download (downcnt,addtime,modtime,ipaddr) VALUES (1,'%s','%s','%s')",
            $time, $time, Aleafs_Lib_Context::userip()
        );

        $query .= sprintf(" ON DUPLICATE KEY UPDATE downcnt=downcnt+1,modtime='%s'", $time);
        $mysql->query($query);
    }
    /* }}} */

}
