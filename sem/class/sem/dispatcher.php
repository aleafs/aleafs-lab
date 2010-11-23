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

class Aleafs_Sem_Dispatcher
{

    /* {{{ 成员变量 */

    private $url;

    private $log;

    private $prefix;

    private $config;

    private static $timeout    = true;

    /* }}} */

    /* {{{ public static void run() */
    /**
     * 处理器入口
     *
     * @access public static
     * @return void
     */
    public static function run($ini, $url, $post = null)
    {
        try {
            $dsp    = new self($ini);
            $dsp->dispach($url, $post);
        } catch (SoapFault $e) {
            self::$timeout  = false;
            throw $e;
        } catch (Exception $e) {
            self::$timeout  = false;
            throw $e;
        }

        self::term();
    }
    /* }}} */

    /* {{{ public static void setAutoLoad() */
    /**
     * 自动加载初始化
     *
     * @access public static
     * @return void
     */
    public static function setAutoLoad()
    {
        $__dir  = dirname(__FILE__);
        require_once $__dir . '/../lib/autoload.php';

        Aleafs_Lib_AutoLoad::init();
        Aleafs_Lib_AutoLoad::register('aleafs_sem',     $__dir);
    }
    /* }}} */

    /* {{{ public void shutdownCallBack() */
    /**
     * 请求结束时的回调函数
     *
     * @access public
     * @return void
     */
    public function shutdownCallBack()
    {
        if (true === self::$timeout) {
            $this->log->error('RUN_TIMEOUT', array(
                'url' => $this->url,
            ));
        }
    }
    /* }}} */

    /* {{{ private void dispach() */
    /**
     * 分发处理
     *
     * @access private
     * @return void
     */
    private function dispach($url, $post = null)
    {
        $this->url  = preg_replace(
            sprintf('/^\/?%s/is', strtr($this->prefix, array('/' => '\\/'))),
            '', $url, 1
        );
        Aleafs_Lib_Debug_Pool::push('global.url',    $this->url);
        Aleafs_Lib_Debug_Pool::push('global.post',   $post);

        $url    = new Aleafs_Lib_Parser_Url($this->url);
        $ctrl   = self::ctrl($url->module);
        $ctrl   = new $ctrl();
        $ctrl->execute($url->action, $url->param, $post);
    }
    /* }}} */

    /* {{{ private static void term() */
    /**
     * 请求退出
     *
     * @access private static
     * @return void
     */
    private static function term()
    {
        self::$timeout  = false;
        if (empty($GLOBALS['__in_debug_tools']) && function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
    /* }}} */

    /* {{{ private static string ctrl() */
    /**
     * 获取控制器类名
     *
     * @access private static
     * @return string
     */
    private static function ctrl($ctrl)
    {
        if (empty($ctrl)) {
            return 'Aleafs_Sem_Controller_Webui';
        }

        return sprintf('Aleafs_Sem_Controller_%s', ucfirst(strtolower($ctrl)));
    }
    /* }}} */

    /* {{{ private void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param String $ini
     * @return void
     */
    private function __construct($ini)
    {
        self::setAutoLoad();
        Aleafs_Lib_Configer::register('default',   $ini);

        $this->config   = Aleafs_Lib_Configer::instance('default');
        $this->log      = Aleafs_Lib_Factory::getLog($this->config->get('log.url', ''));
        $this->prefix   = trim($this->config->get('url.prefix', ''), '/ ');
        foreach ($this->config->get('includes', array()) AS $name => $file) {
            Aleafs_Lib_Configer::register($name,   $file);
        }

        $webroot    = rtrim($this->config->get('url.server', ''), '/');
        if (!empty($this->prefix)) {
            $webroot = sprintf('%s/%s', $webroot, $this->prefix);
        }
        Aleafs_Lib_Context::register('webroot', $webroot);

        if (0 === strcasecmp('online', $this->config->get('run.mode'))) {
            error_reporting(0);
            ini_set('display_errors', 'Off');
        }

        date_default_timezone_set('Asia/Shanghai');
        set_time_limit($this->config->get('timeout', 30));
        register_shutdown_function(array(&$this, 'shutdownCallBack'));
    }
    /* }}} */

}

