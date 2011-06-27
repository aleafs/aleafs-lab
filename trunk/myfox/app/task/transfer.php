<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: transfer.php 18 2010-04-13 15:40:37Z zhangxc83 $
//

namespace Myfox\App\Task;

class Transfer extends \Myfox\App\Task
{

    /* {{{ public Integer execute() */
    /**
     * 任务执行
     *
     * @access public
     * @return Integer
     */
    public function execute()
    {
        if (!$this->isReady('from', 'save', 'path')) {
            return self::FAIL;
        }

        return self::WAIT;
    }
    /* }}} */

    /* {{{ public Integer wait() */
    /**
     * 等待数据转移返回
     *
     * @access public
     * @return Integer
     */
    public function wait()
    {
        return self::SUCC;
    }
    /* }}} */

}
