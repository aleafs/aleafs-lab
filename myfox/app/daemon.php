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

    private static $scores  = array();      /**<    记分板 */

    private static $signal  = array(
        SIGTERM     => 'SIGTERM',
    );

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
        foreach (self::$signal AS $sig => $txt) {
            pcntl_signal($sig, array(&$this, 'sigaction'));
        }

        while ($this->isrun) {
        }
    }
    /* }}} */

    /* {{{ public void sigaction() */
    /**
     * 信号处理
     *
     * @access public
     * @return void
     */
    public function sigaction($sig)
    {
        $sig    = (int)$sig;
        if (empty(self::$signal[$sig])) {
            return;
        }

        // write Log
        if (SIGTERM === $sig) {
            $this->isrun    = false;

            while (($pid = pcntl_fork()) < 0) {
                usleep(100000);
            }

            if (!empty($pid)) {
                exit();
            }

            posix_setsid();
        } else {
            //
        }
    }
    /* }}} */

}
