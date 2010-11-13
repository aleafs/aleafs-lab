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

	/* }}} */

	/* {{{ public static Mixture getPermission() */
	/**
	 * 获取用户权限
	 *
	 * @access public static
	 * @param  String $name
	 * @return Mixture
	 */
	public static function getPermission($user)
	{
	}
	/* }}} */

	/* {{{ public static Boolean addPermission() */
	/**
	 * 添加用户权限
	 *
	 * @access public static
	 * @return Boolean true or false
	 */
	public static function addPermission($user, $perm)
	{
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

        return self::$loader->clear()
            ->table(sprintf('%suseracct', self::TABLE_PREFIX))
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
    }
    /* }}} */

}
