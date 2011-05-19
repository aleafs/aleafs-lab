<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: task.php 18 2010-04-13 15:40:37Z zhangxc83 $
//

namespace Myfox\App;

abstract class Task
{

    /* {{{ 静态常量 */

    const SUCC  = 0;
    const FAIL  = 1;
    const WAIT  = 2;

    /* }}} */

    /* {{{ 成员变量 */

    protected $id;

    private $option;

    private $lastError;

    /* }}} */

    /* {{{ abstract public Integer execute() */
    /**
     * 执行任务
     *
     * @access public
     * @return Integer
     */
    abstract public function execute();
    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  Integer $id
     * @param  Array $option
     * @return void
     */
    public function __construct($id, $option)
    {
        $this->id       = (int)$id;
        $this->option   = (array)$option;
    }
    /* }}} */

    /* {{{ public Integer wait() */
    /**
     * 等待异步执行结果
     *
     * @access public
     * @return Integer
     */
    public function wait()
    {
        return self::FAIL;
    }
    /* }}} */

    /* {{{ public String getLastError() */
    /**
     * 获取错误描述
     *
     * @access public
     * @return String
     */
    public function getLastError()
    {
        return $this->lastError;
    }
    /* }}} */

    /* {{{ protected void setError() */
    /**
     * 设置错误消息
     *
     * @access protected
     * @param  String $error
     * @return void
     */
    protected function setError($error)
    {
        $this->lastError    = trim($error);
    }
    /* }}} */

    /* {{{ protected Mixture option() */
    /**
     * 返回任务属性
     *
     * @access protected
     * @return Mixture
     */
    protected function option($key, $default = null)
    {
        return isset($this->option[$key]) ? $this->option[$key] : $default;
    }
    /* }}} */

}
