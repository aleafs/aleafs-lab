<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: import.php 18 2010-04-13 15:40:37Z zhangxc83 $
//

namespace Myfox\App\Task;

class Import extends \Myfox\App\Task
{

    /* {{{ public Integer execute() */
    public function execute()
    {
        if (!$this->isReady('file', 'host', 'save', 'route')) {
            return self::FAIL;
        }
    }
    /* }}} */

    /* {{{ public Integer wait() */
    public function wait()
    {
    }
    /* }}} */

}
