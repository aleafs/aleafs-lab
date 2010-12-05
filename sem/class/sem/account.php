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

    const STATUS_OK = 0;
    const NOT_LOGIN	= 1;
    const ERR_PWD	= 2;
    const CHECK_IP	= 3;
    const KICK_OFF	= 4;
    const REPUT_PWD	= 5;

    /* XXX:这个一定不要修改 */
    const SAFE_SALT = 'e82e1ee05f3b1361f682beecaeda257f';

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

    /* {{{ public static String password() */
    /**
     * 生成密码
     *
     * @access public static
     * @return String
     */
    public static function password($pw)
    {
        return bin2hex(md5(self::SAFE_SALT . $pw, true));
    }
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
        self::$status   = self::STATUS_OK;
        if (!Aleafs_Lib_Session::get('isLogin')) {
            self::$status	= self::NOT_LOGIN;
        }

        return self::$status;
    }
    /* }}} */

    /* {{{ public static Object  getUser() */
    /**
     * 校验密码
     *
     * @access public static
     * @return Object or Boolean false
     */
    public static function getUser($un, $pw)
    {
        $un = Aleafs_Sem_User::getUserInfo($un, array('userid', 'username', 'password', 'checkip', 'sglogin'));
        if (empty($un) || 0 !== strcmp($un['password'], self::password($pw))) {
            return false;
        }
        unset($un['password']);

        return json_decode(json_encode($un));
    }
    /* }}} */

    /* {{{ public static String getMessage() */
    /**
     * 获取用户的登录消息
     *
     * @access public static
     * @return String
     */
    public static function getMessage($code = null)
    {
        $code   = empty($code) ? self::$status : (int)$code;
        return empty(self::$message[$code]) ? '' : self::$message[$code];
    }
    /* }}} */

}

