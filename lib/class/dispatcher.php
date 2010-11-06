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

namespace Aleafs\Lib;

use \Aleafs\Lib\AutoLoad;
use \Aleafs\Lib\Configer;
use \Aleafs\Lib\Factory;

use \Aleafs\Lib\Debug\Pool;
use \Aleafs\Lib\Parser\Url;

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
        require_once __DIR__ . '/autoload.php';
        AutoLoad::init();

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
            sprintf('/^\/?%s/is', $this->prefix),
            '', $url, 1
        );
        Pool::push('global.url',    $this->url);
        Pool::push('global.post',   $post);

        $url    = new Url($this->url);
        $ctrl   = self::ctrl($url->module);
        $ctrl   = new $ctrl();
        $ctrl->execute($url->action, $url->param, $post);
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
            return '\Aleafs\Lib\Controller';
        }

        return sprintf('Controller\\%s', ucfirst(strtolower($ctrl)));
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

