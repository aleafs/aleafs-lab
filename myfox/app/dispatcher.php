<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 请求转发器		    					    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: dispatcher.php 18 2010-04-13 15:40:37Z zhangxc83 $

namespace Myfox\App;

use \Myfox\Lib\AutoLoad;
//use \Myfox\Lib\Configer;

use \Myfox\Lib\Parser\Url;

class Dispatcher
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
        } catch (\Exception $e) {
        }

        self::$timeout  = false;
        if (empty($GLOBALS['__in_debug_tools']) && function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
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
        require_once __DIR__ . '/autoload.php';

        AutoLoad::init();
        AutoLoad::register('myfox\\app',    __DIR__);
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

        $url    = new \Myfox\Lib\Parser\Url($this->url);
        $module = $url->module();
        if (empty($module)) {
            $ctrl   = sprintf('\\Myfox\\App\\Controller');
        } else {
            $ctrl   = sprintf('\\Myfox\\App\\Control\\%s', ucfirst(strtolower($module)));
        }

        $ctrl   = new $ctrl();
        $ctrl->execute($url->action(), $url->param(), $post);
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
        Configer::register('default',   $ini);

        $this->config   = Configer::instance('default');
        $this->log      = Factory::getLog($this->config->get('log.url', ''));
        $this->prefix   = trim($this->config->get('url.prefix', ''), '/ ');
        foreach ($this->config->get('includes', array()) AS $name => $file) {
            Configer::register($name,   $file);
        }

        set_time_limit($this->config->get('timeout', 30));
        register_shutdown_function(array(&$this, 'shutdownCallBack'));
    }
    /* }}} */

}

