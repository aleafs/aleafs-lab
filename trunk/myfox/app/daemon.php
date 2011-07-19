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

    /* {{{ public void sigaction() */
    /**
     * 信号处理
     *
     * @access public
     * @return void
     */
    public function sigaction($signal)
    {
        $signal = (int)$signal;
        if (empty(self::$signal[$signal])) {
            posix_kill(posix_getpid(), SIGTERM);
            return;
        }

        switch ($signal) {
        case SIGTERM:
            $this->isrun    = false;
            $this->cleanup();

            while (($pid = pcntl_fork()) < 0) {
                usleep(100000);
            }

            if (!empty($pid)) {
                exit();
            }

            posix_setsid();
            break;

        default:
            break;
        }
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
        pcntl_signal_dispatch();

        while ($this->isrun) {
        }
    }
    /* }}} */

    /* {{{ private void cleanup() */
    /**
     * 程序退出清理
     *
     * @access private
     * @return void
     */
    private function cleanup()
    {
    }
    /* }}} */

}
