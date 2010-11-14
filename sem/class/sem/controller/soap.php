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

    /* {{{ 静态常量 */

    const WSDL_EXPIRE   = 3600;

    /* }}} */

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

        $__dir  = dirname(__FILE__);
        Aleafs_Lib_Render_Html::init(array(
            'tpl_path'  => $__dir . '/../../../resource/themes',
            'obj_path'  => $__dir . '/../../../cache/themes',
            'theme'     => 'default',
        ));
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

        ob_start();
        try {
            $soap   = new SoapServer(self::wsdlfile($action), array(
                'soap_version'	=> SOAP_1_2,
                'encoding'		=> 'utf-8',
                'cache_wsdl'    => 0,
            ));

            Aleafs_Lib_Context::register('soap.server', $soap);

            $class  = sprintf('Aleafs_Sem_Service_%s', ucfirst($action));
            $soap->setObject(new $class());
            $soap->handle($post);
        } catch (Exception $e) {
            self::fault(401, $e->getMessage());
        }

        $data   = ob_get_clean();
        echo $data;
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
        $render->assign('webroot', Aleafs_Lib_Context::get('webroot'));

        header('Content-type: application/xml;charset=utf-8');
        try {
            $render->render($name, 'soap', true);
        } catch (Exception $e) {
            throw $e;
            self::fault(404, $e->getMessage());
        }
    }
    /* }}} */

    /* {{{ private static string wsdlfile() */
    /**
     * 获取WSDL文件
     *
     * @access private static
     * @return string
     */
    private static function wsdlfile($action)
    {
        $cache  = sprintf('%s/../../../cache/wsdl/%s.wsdl', dirname(__FILE__), $action);
        if (!is_file($cache) || time() - filemtime($cache) > self::WSDL_EXPIRE) {
            $dr = dirname($cache);
            if (!is_dir($dr)) {
                mkdir($dr, 0744, true);
            }

            file_put_contents($cache, file_get_contents(
                sprintf('%s/soap/%s/wsdl', Aleafs_Lib_Context::get('webroot'), $action)
            ));
        }

        return $cache;
    }
    /* }}} */

    /* {{{ private static void fault() */
    /**
     * 抛出SOAP异常
     *
     * @access private static
     * @return void
     */
    private static function fault($code, $error)
    {
        throw new SoapFault('server', sprintf('[%d] %s', $code, $error));
    }
    /* }}} */

}

