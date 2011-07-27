<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: transfer.php 18 2010-04-13 15:40:37Z zhangxc83 $
//

namespace Myfox\App\Task;

use \Myfox\App\Model\Server;
use \Myfox\App\Model\Table;

class Transfer extends \Myfox\App\Task
{

    /* {{{ 静态变量 */

    private static $dist    = array();

    /* }}} */

    /* {{{ 成员变量 */

    private $table;

    /* }}} */

    /* {{{ public Integer execute() */
    /**
     * 任务执行
     *
     * @access public
     * @return Integer
     */
    public function execute()
    {
        if (!$this->isReady('from', 'save', 'table', 'path')) {
            return self::FAIL;
        }

        $this->table    = Table::instance($this->option('table'));
        if (!$this->table->get('autokid')) {
            $this->setError(sprintf('Undefined table named as "%s"', $this->option('table')));
            return self::IGNO;
        }

        self::metadata($flush);
        if ($flush) {
            self::$dist = array();
        }

        $target = array();
        foreach (explode(',', trim($this->option('save'), '{}')) AS $id) {
            if (!empty(self::$nodes[$id])) {
                $target += self::$nodes[$id];
            }
        }
        if (empty($target)) {
            $this->setError(sprintf('Empty transfer target nodes, input:%s', $this->option('save')));
            return self::IGNO;
        }

        $source = array();
        foreach (explode(',', trim($this->option('from'), '{}')) AS $id) {
            if (!empty(self::$nodes[$id])) {
                $source += array_flip(self::$nodes[$id]);
            }
        }
        if (empty($source)) {
            $this->setError(sprintf('Empty transfer source nodes, input:', $this->option('from')));
            return self::FAIL;
        }

        $return = self::SUCC;
        foreach ($target AS $name) {
            if (Server::TYPE_VIRTUAL == self::$hosts[$name]['type']) {
                continue;
            }

            $option = array_intersect_key((array)self::dist($name), $source);
            foreach ((array)$option AS $from => $dist) {
                if ($this->replicate($from, $name, $this->option('path'))) {
                    $return = self::WAIT;
                    continue 2;
                }
            }

            return self::FAIL;
        }

        return $return;
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
        // xxx: 校验一致性
        // 改路由
        return self::SUCC;
    }
    /* }}} */

    /* {{{ private Boolean replicate() */
    /**
     * 复制表
     *
     * @access private
     * @return Boolean true or false
     */
    private function replicate($from, $save, $path)
    {
        return true;
    }
    /* }}} */

    /* {{{ private static Mixture dist() */
    /**
     * 获取机器距离表
     *
     * @access private static
     * @return Mixture
     */
    private static function dist($name)
    {
        if (empty(self::$dist[$name])) {
            if (empty(self::$hosts[$name])) {
                return array();
            }

            $dt = array();
            $my = self::$hosts[$name];
            foreach (self::$hosts AS $key => $opt) {
                if ($my['node'] == $opt['node'] || 0 == strcasecmp($key, $name)) {
                    continue;
                }
                $dt[$key]   = abs($opt['pos'] - $my['pos']);
            }
            asort($dt, SORT_NUMERIC);
            self::$dist[$name]  = $dt;
        }

        return self::$dist[$name];
    }
    /* }}} */


}
