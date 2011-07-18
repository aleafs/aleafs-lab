<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 后台运行daemon类						    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: daemon.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

namespace Myfox\App;

class Daemon
{

    /* {{{ 静态变量 */

    private static $scores = array();      /**<    记分板 */

    /* }}} */

    /* {{{ 成员变量 */

    private $master = true;

    private $worker = null;

    private $isrun  = false;

    /* }}} */

    /* {{{ public static void run() */
    /**
     * 进入工作模式
     *
     * @access public static
     * @return void
     */
    public static function run($ini, $class, $args = null)
    {
        $master = new self($ini, $class);
        $master->dispatch();
    }
    /* }}} */

    /* {{{ private void __construct() */
    /**
     * 构造函数
     *
     * @access private
     * @return void
     */
    private function __construct($ini, $class)
    {
        if (!extension_loaded('pcntl')) {
            return false;
        }

        Application::init($ini);
        $this->worker   = null;
    }
    /* }}} */

    /* {{{ private void dispatch() */
    /**
     * 任务分发
     *
     * @access private
     * @return void
     */
    private function dispatch()
    {
        set_time_limit(0);
        while ($this->isrun) {
        }
    }
    /* }}} */

}
