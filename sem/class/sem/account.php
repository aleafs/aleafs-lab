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

class Aleafs_Sem_Account
{

	/* {{{ 静态常量 */

	const NOT_LOGIN	= 101;
	const ERR_PWD	= 102;
	const CHECK_IP	= 201;
	const KICK_OFF	= 202;
	const REPUT_PWD	= 203;

	/* }}} */

	/* {{{ 静态变量 */

	private static $message	= array(
		self::NOT_LOGIN	=> '请输入用户名和密码登录',
		self::ERR_PWD	=> '用户名或者密码错误',
		self::CHECK_IP	=> '请重新输入密码进行身份验证',
		self::KICK_OFF	=> '您的账户已在别处登录',
		self::REPUT_PWD	=> '请重新输入密码进行身份验证',
	);

	private static $status	= 0;

	/* }}} */

	/* {{{ public static Boolean isLogin() */
	/**
	 * 判断用户是否登录
	 *
	 * @access public static
	 * @return Boolean true or false
	 */
	public static function isLogin()
	{
		if (!Aleafs_Lib_Session::get('isLogin')) {
			self::$status	= self::NOT_LOGIN;
		}

		return true;
	}
	/* }}} */

	/* {{{ public static String getMessage() */
	/**
	 * 获取用户的登录消息
	 *
	 * @access public static
	 * @return String
	 */
	public static function getMessage()
	{
		return empty(self::$message[self::$status]) ? '' : self::$message[self::$status];
	}
	/* }}} */

}

