<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 通用任务队列队列类						    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: queque.php 18 2010-04-13 15:40:37Z zhangxc83 $

namespace Myfox\App;

use \Myfox\Lib\Context;

class Queque
{

    /* {{{ 静态常量 */

    const FLAG_NEW	= 0;
    const FLAG_WAIT	= 1;
    const FLAG_LOCK	= 2;
    const FLAG_IGN  = 7;
    const FLAG_DONE	= 9;

    const MAX_TRIES = 3;

    const IMPORT    = 1;
    const TRANSFER  = 2;
    const DELETE    = 3;

    /* }}} */

    /* {{{ 静态变量 */

    private static $mypos;

    private static $mysql;

    private static $typemap = array(
        self::IMPORT    => 'Import',
        self::TRANSFER  => 'Transfer',
        self::DELETE    => 'Delete',
    );
    /* }}} */

    /* {{{ public static Mixture fetch() */
    /**
     * 获取一条任务
     *
     * @access public static
     * @return Mixture
     */
    public static function fetch()
    {
        self::init();

        $row    = self::$mysql->getRow(self::$mysql->query(sprintf(
            'SELECT autokid AS id,task_type,tmp_status,task_info FROM %stask_queque WHERE '.
            ' task_flag=%d AND trytimes < %d ORDER BY priority ASC, trytimes ASC, ' .
            ' ABS(agentpos - %u) ASC, autokid ASC LIMIT 1',
            self::$mysql->option('prefix', ''),
            self::FLAG_WAIT, self::MAX_TRIES, self::$mypos
        )));

        if (empty($row)) {
            return null;
        }

        if (empty(self::$typemap[$row['task_type']])) {
            throw new \Myfox\Lib\Exception(sprintf('Undefined task type as "%s"', $row['task_type']));
        }

        $class  = sprintf('Myfox\App\Task\%s', self::$typemap[$row['task_type']]);
        return new $class($row['id'], json_decode($row['task_info'], true), $row['tmp_status']);
    }
    /* }}} */

    /* {{{ public static Boolean insert() */
    /**
     * 插入一条队列
     *
     * @access public static
     * @return Boolean true or false
     */
    public static function insert($type, $info, $agent = 0, $option = null)
    {
        self::init();

        $column = array(
            'priority'  => 100,
            'trytimes'  => 0,
            'task_flag' => self::FLAG_WAIT,
            'adduser'   => '',
        );

        foreach ((array)$option AS $key => $val) {
            if (isset($column[$key])) {
                $column[$key]   = self::$mysql->escape($val);
            }
        }
        $column['addtime']  = date('Y-m-d H:i:s');
        $column['agentpos'] = (int)$agent;
        $column['task_type']= (int)$type;
        $column['task_info']= self::$mysql->escape(json_encode($info));

        return (bool)self::$mysql->query(sprintf(
            "INSERT INTO %stask_queque (%s) VALUES ('%s')",
            self::$mysql->option('prefix', ''),
            implode(',', array_keys($column)), implode("','", $column)
        ));
    }
    /* }}} */

    /* {{{ public static Boolean update() */
    /**
     * 任务队列更改
     *
     * @access public static
     * @return Boolean true or false
     */
    public static function update($id, $option, $comma = null)
    {
        self::init();

        $column = array(
            'agentpos'  => true,
            'priority'  => true,
            'trytimes'  => true,
            'begtime'   => true,
            'endtime'   => true,
            'task_flag' => true,
            'task_type' => true,
            'adduser'   => true,
            'last_error'=> true,
            'tmp_status'=> true,
        );

        $comma  = (array)$comma;
        $update = array();
        foreach ((array)$option AS $key => $val) {
            if (empty($column[$key])) {
                continue;
            }

            if (!empty($comma[$key])) {
                $update[$key]   = sprintf('%s = %s', $key, $val);
            } else {
                $update[$key]   = sprintf("%s = '%s'", $key, self::$mysql->escape($val));
            }
        }
        if (empty($update) || empty($id)) {
            return false;
        }

        return self::$mysql->query(sprintf(
            'UPDATE %stask_queque SET %s WHERE autokid = %d',
            self::$mysql->option('prefix', ''),
            implode(',', $update), $id
        ));
    }
    /* }}} */

    /* {{{ private static void init() */
    private static function init()
    {
        if (empty(self::$mysql)) {
            self::$mysql    = \Myfox\Lib\Mysql::instance('default');
        }

        if (empty(self::$mypos)) {
            self::$mypos    = Context::addr();
        }
    }
    /* }}} */

}

