<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 账户会话管理类	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: user.php 22 2010-04-15 16:28:45Z zhangxc83 $

class Aleafs_Sem_Options
{

    /* {{{ 静态变量 */

    private static $desc    = array();

    private static $data    = array();

    private static $table   = 'options';

    private static $mysql   = null;

    /* }}} */

    /* {{{ public static void init() */
    /**
     * 初始化
     *
     * @access public static
     * @return void
     */
    public static function init($mysql = 'mysql', $table = null)
    {
        if (empty(self::$mysql)) {
            self::$mysql    = new Aleafs_Lib_Db_Mysql(trim($mysql));
        }

        if (empty(self::$table)) {
            self::$table    = trim($table);
        }

        self::$mysql->clear()->table(self::$table);
    }
    /* }}} */

    /* {{{ public static Mixture get() */
    /**
     * 读取变量值
     *
     * @access public static
     * @return Mixture
     */
    public static function get($key)
    {
        $key = strtolower(trim($key));
        if (!isset(self::$data[$key])) {
            self::init();
            self::$data[$key] = self::$mysql->where('cfgname', $key)
                ->select('cfgdata')->getOne();
        }

        return self::$data[$key];
    }
    /* }}} */

    /* {{{ public static Integer set() */
    /**
     * 设置信息
     *
     * @access public static
     * @return Integer
     */
    public static function set($key, $data, $desc = null)
    {
        self::init();

        $key = strtolower(trim($key));
        $now = date('Y-m-d H:i:s');

        $sql = sprintf(
            "INSERT INTO %s (cfgname, cfgdata, addtime, modtime) VALUES ('%s', '%s', '%s', '%s')",
            self::$table, self::$mysql->escape($key), self::$mysql->escape($data), $now, $now
        );
        $sql.= sprintf(
            " ON DUPLICATE KEY UPDATE cfgdata='%s', modtime='%s'",
            self::$mysql->escape($data), $now
        );

        return self::$mysql->query($sql);
    }
    /* }}} */

}

