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

class Aleafs_Sem_Controller_Soap extends Aleafs_Lib_Controller
{

    /* {{{ 成员变量 */

    private $server;

    /* }}} */

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
        Aleafs_Lib_Render_Html::init(array(
            'tpl_path'  => __DIR__ . '/../../../resource/themes',
            'obj_path'  => __DIR__ . '/../../../cache/themes',
            'theme'     => 'default',
        ));

//        $this->server	= new Aleafs_Lib_Soap_Server();
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
        if (!empty($param['wsdl'])) {
            return self::wsdl($action);
        }

        return parent::execute($action, $param, $post);
    }
    /* }}} */

    /* {{{ private static void wsdl() */
    /**
     * 输出WSDL文件
     *
     * @access private static
     * @return void
     */
    private static function wsdl($name)
    {
        $render = new Aleafs_Lib_Render_Html();
        $render->assign('soap_namespace', 'http://api.aleafs.com/soap');

        header('Context-Type: html/xml, charset=utf-8;');
        $render->render($name, 'soap', true);
        try {
            $render->render($name, 'soap', true);
        } catch (Exception $e) {
            //TODO: soapFault
        }
    }
    /* }}} */

    /* {{{ protected void actionAccess() */
    /**
     * 基本服务请求
     *
     * @access protected
     * @return void
     */
    protected function actionAccess($param, $post = null)
    {
    }
    /* }}} */

    /* {{{ protected void actionBaidu() */
    /**
     * 百度服务请求
     *
     * @access protected
     * @return void
     */
    protected function actionBaidu($param, $post = null)
    {
    }
    /* }}} */

}

