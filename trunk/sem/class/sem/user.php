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

    const PERM_TRIAL    = 0;

    const TABLE_PREFIX  = '';

    /* }}} */

    /* {{{ 静态变量 */

    private static $loader;

    private static $column;

    /* }}} */

    /* {{{ public static string username() */
    /**
     * 获取用户名
     *
     * @access public static
     * @return String
     */
    public static function username($appuser, $appname)
    {
        return sprintf('%s/%s', strtolower(trim($appname)), trim($appuser));
    }
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

    /* {{{ public static Integer addPermission() */
    /**
     * 添加用户权限
     *
     * @access public static
     * @return Integer
     */
    public static function addPermission($appuser, $appname, $perms)
    {
        $perms  = array_intersect_key(
            (array)$perms, self::column(sprintf('%suser_permission', self::TABLE_PREFIX))
        );
        unset($perms['autokid']);

        if (empty($perms)) {
            return false;
        }

        $perms['addtime']   = date('Y-m-d H:i:s');
        $perms['se_user']   = $appuser;
        $perms['se_name']   = $appname;

        self::initDb();

        return self::$loader->insert($perms)->lastId();
    }
    /* }}} */

    /* {{{ public static Integer cleanPermission() */
    public static function cleanPermission($appuser, $appname)
    {
        self::initDb();

        return self::$loader->table(sprintf('%suser_permission', self::TABLE_PREFIX))
            ->where('se_user', $appuser)->where('se_name', $appname)->delete()->affectedRows();
    }
    /* }}} */

    /* {{{ public static Mixture getUserInfo() */
    /**
     * 根据用户名获取信息
     *
     * @access public static
     * @return Mixture
     */
    public static function getUserInfo($name)
    {
        self::initDb();

        return self::$loader->table(sprintf('%suseracct', self::TABLE_PREFIX))
            ->where('username', $name)
            ->select('userid', 'usertype', 'userstat', 'email')
            ->getRow();
    }
    /* }}} */

    /* {{{ public static Integer initUser() */
    /**
     * 初始化用户
     *
     * @access public static
     * @return Integer
     */
    public static function initUser($uname, $column)
    {
        $table  = sprintf('%suseracct', self::TABLE_PREFIX);
        $column = array_intersect_key((array)$column, self::column($table));
        if (empty($column)) {
            return false;
        }

        $column['username'] = trim($uname);
        $column['addtime']  = date('Y-m-d H:i:s');
        $column['modtime']  = $column['addtime'];

        self::initDb();

        return self::$loader->insert($column)->lastId();
    }
    /* }}} */

    /* {{{ public static Integer cleanUser() */
    /**
     * 清理用户
     *
     * @access public static
     * @return Integer
     */
    public static function cleanUser($uname)
    {
        self::initDb();

        return self::$loader->table(sprintf('%suseracct', self::TABLE_PREFIX))
            ->where('username', $uname)->delete()->affectedRows();
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
            $column = self::$loader->getAll(self::$loader->query(sprintf('DESC %s', $table)));
            foreach ($column AS $row) {
                self::$column[$table][$row['Field']] = $row['Type'];
            }
        }

        return self::$column[$table];
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

}
