<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: delete.php 18 2010-04-13 15:40:37Z zhangxc83 $
//

namespace Myfox\App\Task;

use \Myfox\App\Model\Server;

class Delete extends \Myfox\App\Task
{

    private $optimize   = false;

    /* {{{ public Integer execute() */
    public function execute()
    {
        $where  = $this->option('where');
        if (empty($where)) {
            $query  = sprintf('DROP TABLE IF EXISTS %s', $this->option('table'));
        } else {
            $query  = sprintf('DELETE FROM %s WHERE ', $this->option('table'));
            $this->optimize = true;
        }

        return self::WAIT;
    }
    /* }}} */

    /* {{{ public Integer wait() */
    public function wait()
    {
        if ($this->optimize) {
        }

        return self::SUCC;
    }
    /* }}} */

}
