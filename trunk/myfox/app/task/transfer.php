<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: transfer.php 18 2010-04-13 15:40:37Z zhangxc83 $
//

namespace Myfox\App\Task;

use \Myfox\App\Model\Router;
use \Myfox\App\Model\Server;
use \Myfox\App\Model\Table;

class Transfer extends \Myfox\App\Task
{

    /* {{{ 静态变量 */

    private static $dist    = array();

    /* }}} */

    /* {{{ 成员变量 */

    private $table;

    private $pools;

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
            $this->setError(sprintf('Empty transfer source nodes, input:%s', $this->option('from')));
            return self::FAIL;
        }

        $this->pools    = array();
        $ignore = array_flip(explode(',', (string)$this->status));
        foreach ($target AS $name) {
            if (isset($ignore[$name]) || Server::TYPE_VIRTUAL == self::$hosts[$name]['type']) {
                continue;
            }

            $option = array_intersect_key((array)self::dist($name), $source);
            foreach ((array)$option AS $from => $dist) {
                if ($this->replicate($from, $name, $this->option('path'))) {
                    $ignore[$name]  = true;
                    break;
                }
            }
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
        $result = array();
        $allok  = true;
        foreach ($this->pools AS $pool) {
            list($db, $key, $host)  = $pool;
            if (false === $key || false === $db->wait($key)) {
                $allok  = false;
                $this->setError($db->lastError($key));
            } else {
                $result[$host]  = true;
            }
        }
        $this->pools    = array();
        if (true !== $allok) {
            return self::FAIL;
        }

        // xxx: 校验一致性
        // 改路由

        $this->result   = implode(',', $result);

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
        list($dbname, $tbname)  = explode('.', $path, 2);

        $create = array();
        foreach ($this->table->column() AS $key => $val) {
            $create[]   = $val['sqlchar'];
        }
        foreach ($this->table->index() AS $key => $val) {
            $create[]   = trim(sprintf(
                '%s KEY %s (%s)', $val['idxtype'], $key, trim($val['idxchar'], '()')
            ));
        }

        $column = implode(',', $create);
        $source = Server::instance($from);
        $create = sprintf(
            "CREATE TABLE %s.%s_fed (%s) ENGINE = FEDERATED DEFAULT CHARSET=UTF8 CONNECTION='mysql://%s@%s:%d/%s/%s'",
            $dbname, $tbname, $column, $source->option('user_ro'), $source->option('conn_host'),
            $source->option('conn_port'), $dbname, $tbname
        );

        $querys = array(
            sprintf(
                'DROP TABLE IF EXISTS %s.%s, %s.%s_fed',
                $dbname, $tbname, $dbname, $tbname
            ),
            sprintf('CREATE DATABASE IF NOT EXISTS %s', $dbname),
            $create,
            sprintf(
                'CREATE TABLE %s.%s (%s) ENGINE = MyISAM DEFAULT CHARSET=UTF8',
                $dbname, $tbname, $column
            ),
        );

        $target = Server::instance($save)->getlink();
        foreach ($querys AS $sql) {
            if (false === $target->query($sql)) {
                $this->setError($target->lastError());
                return false;
            }
        }

        $this->pools[]  = array($target, $target->async(sprintf(
            'INSERT INTO %s.%s SELECT * FROM %s.%s_fed',
            $dbname, $tbname, $dbname, $tbname
        )), $save);

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

            $aa = array();
            $bb = array();
            $my = self::$hosts[$name];
            foreach (self::$hosts AS $key => $opt) {
                if ($my['node'] == $opt['node'] || 0 == strcasecmp($key, $name)) {
                    continue;
                }

                $ds = abs($opt['pos'] - $my['pos']);
                if (Router::ARCHIVE == $opt['mark']) {
                    $bb[$key]   = $ds;
                } else {
                    $aa[$key]   = $ds;
                }
            }
            asort($aa, SORT_NUMERIC);
            asort($bb, SORT_NUMERIC);
            self::$dist[$name]  = $aa + $bb;
        }

        return self::$dist[$name];
    }
    /* }}} */

}
