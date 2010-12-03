<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 默认控制器		    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: autoload.php 22 2010-04-15 16:28:45Z zhangxc83 $

class Aleafs_Lib_Controller
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
        if (empty($action)) {
            $action	= 'index';
        }

        $method	= sprintf('action%s', ucfirst($action));
        if (!method_exists($this, $method)) {
            throw new Aleafs_Lib_Exception(sprintf('Undefined action named as "%s"', $action));
        }

        return $this->$method($param, $post);
    }
    /* }}} */

    /* {{{ public void redirect() */
    /**
     * 重定向请求
     *
     * @access public
     * @return void
     */
    public function redirect($module, $action, $param = null)
    {
        header(sprintf('Location: %s/%s',
            rtrim(Aleafs_Lib_Context::get('webroot'), '/'),
            ltrim(Aleafs_Lib_Parser_Url::build($module, $action, $param), '/')
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
        echo '<!--STATUS OK-->';
    }
    /* }}} */

}

