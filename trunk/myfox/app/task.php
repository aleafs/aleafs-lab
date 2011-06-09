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

    /* {{{ public Mixture __get() */
    /**
     * 魔术方法获取私有变量
     *
     * @access public
     * @return Mixture
     */
    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : null;
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

    /* {{{ public Mixture option() */
    /**
     * 返回任务属性
     *
     * @access public
     * @return Mixture
     */
    public function option($key, $default = null)
    {
        return isset($this->option[$key]) ? $this->option[$key] : $default;
    }
    /* }}} */

    /* {{{ public Boolean lock() */
    /**
     * 锁定任务
     *
     * @access public
     * @return Boolean true or false
     */
    public function lock()
    {
        return (bool)Queque::update(
            $this->id,
            array(
                'begtime'   => sprintf(
                    "IF(task_flag=%d,begtime,'%s')",
                    Queque::FLAG_LOCK, date('Y-m-d H:i:s')
                ),
                'task_flag' => Queque::FLAG_LOCK,
            ),
            array('begtime' => true)
        );
    }
    /* }}} */

    /* {{{ public Boolean unlock() */
    /**
     * 完成后任务解锁
     *
     * @access public
     * @return Boolean true or false
     */
    public function unlock($errno = 0, $error = '')
    {
        $flag   = empty($errno) ? Queque::FLAG_DONE : Queque::FLAG_WAIT;
        return Queque::update($this->id, array(
            'trytimes'  => sprintf('IF(task_flag=%d,trytimes,trytimes+1)', $flag),
            'endtime'   => sprintf("IF(loadflag=%d,endtime,'%s')", $flag, date('Y-m-d H:i:s')),
            'task_flag' => $flag,
            'last_error'=> trim($error),
        ), array(
            'trytimes'  => true,
            'endtime'   => true,
            'task_flag' => true,
        ));
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

}
