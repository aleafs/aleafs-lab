<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 用户类			    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: user.php 22 2010-04-15 16:28:45Z zhangxc83 $

class Aleafs_Sem_User
{

    /* {{{ 静态常量 */

    const TYPE_ADMIN	= 10000;
    const TYPE_CLIENT	= 20000;

    const STAT_NORMAL	= 200;
    const STAT_DISABLE	= 500;

    const TABLE_PREFIX  = '';

    /* }}} */

    /* {{{ 静态变量 */

    private static $loader;

    private static $column;

    /* }}} */

    /* {{{ public static Mixture getPermission() */
    /**
     * 获取用户权限
     *
     * @access public static
     * @param  String $name
     * @return Mixture
     */
    public static function getPermission($appuser, $appname, $uid = 0)
    {
        self::initDb();

        self::$loader->order('autokid', 'ASC')
            ->table(sprintf('%suser_permission', self::TABLE_PREFIX))
            ->where('se_user', $appuser)
            ->where('se_name', $appname);
        if ($uid > 0) {
            self::$loader->where('userid', $uid);
        }

        return self::$loader->select('pm_stat', 'pm_type', 'pm_func', 'begdate', 'enddate')
            ->getAll();
    }
    /* }}} */

    /* {{{ public static Boolean addPermission() */
    /**
     * 添加用户权限
     *
     * @access public static
     * @return Boolean true or false
     */
    public static function addPermission($appuser, $appname, $perms)
    {
        if (empty($perms)) {
            return false;
        }

        self::initDb();
    }
    /* }}} */

    /* {{{ public static Boolean cleanPermission() */
    public static function cleanPermission($appuser, $appname)
    {
        self::initDb();

        return self::$loader->table(sprintf('%suser_permission', self::TABLE_PREFIX))
            ->where('se_user', $appuser)->where('se_name', $appname)->delete();
    }
    /* }}} */

    /* {{{ public static Mixture getInfoByName() */
    /**
     * 根据用户名获取信息
     *
     * @access public static
     * @return Mixture
     */
    public static function getInfoByName($name)
    {
        self::initDb();

        return self::$loader->table(sprintf('%suseracct', self::TABLE_PREFIX))
            ->where('username', $name)
            ->select('userid', 'usertype', 'userstat', 'email')
            ->getRow();
    }
    /* }}} */

    /* {{{ private static void initDb() */
    /**
     * 初始化DB对象
     *
     * @access private static
     * @return void
     */
    private static function initDb()
    {
        if (empty(self::$loader)) {
            self::$loader   = new Aleafs_Lib_Db_Mysql('mysql');
        }
        self::$loader->clear();
    }
    /* }}} */

    /* {{{ private static Mixture column() */
    /**
     * 获取表结构
     *
     * @access private static
     * @return Mixture
     */
    private static function column($table)
    {
        $table  = trim($table);
        if (empty(self::$column[$table])) {
            self::initDb();
            $column = self::$loader->query(sprintf('DESC %s', $table))->getAll();
            foreach ($column AS $row) {
                self::$column[$table][$row['Field']] = $row['Type'];
            }
        }

        return self::$column[$table];
    }
    /* }}} */

}
