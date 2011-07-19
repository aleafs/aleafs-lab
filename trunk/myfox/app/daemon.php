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

    private $child  = 0;

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
        switch ((int)$signal) {
        case SIGTERM:
            $this->cleanup();
            $this->nirvana();
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
        $check  = version_compare(phpversion(), '5.3.0', 'ge');
        if (empty($check)) {
            declare(ticks = 1);
        }

        foreach (self::$signal AS $sig => $txt) {
            pcntl_signal($sig, array(&$this, 'sigaction'));
        }

        $this->isrun    = true;
        while ($this->isrun) {
            $check && pcntl_signal_dispatch();
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
        if (empty($this->master)) {
            return;
        }

        $this->isrun    = false;
        while ($this->child > 0) {
            if (($pid = pcntl_wait($status, WNOHANG | WUNTRACED)) > 0) {
                $this->child--;
                if (pcntl_wifexited($status)) {
                    // normal
                } else {
                }
            }
        }
    }
    /* }}} */

    /* {{{ private void nirvana() */
    /**
     * 重新起一个主进程
     *
     * @access private
     * @return void
     */
    private function nirvana()
    {
        $maxtry = 100;
        $count  = 0;
        while ($count++ < $maxtry && ($pid = pcntl_fork()) < 0) {
            usleep(100000);
        }

        if (0 === $pid) {
            posix_setsid();
            $this->master   = true;
        } else {
            $this->master   = false;
        }
    }
    /* }}} */

}
