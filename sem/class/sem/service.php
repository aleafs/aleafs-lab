<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | SOAP服务基类	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: service.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Service
{

    /* {{{ 静态常量 */

    const E_SUCCESS     = 0;
    const E_NOT_TRIAL   = 401;
    const E_SYS_ERROR   = 501;

    /* }}} */

    /* {{{ 静态常量 */

    private static $errorMessage    = array(
        self::E_SUCCESS     => '',
        self::E_NOT_TRIAL   => '您不是试用用户',
        self::E_SYS_ERROR   => '系统错误',
    );

    /* }}} */

    /* {{{ 成员变量 */

    protected $authenticated	= false;

    protected $permissions      = array();

    protected $errno    = self::E_SUCCESS;

    /* }}} */

    /* {{{ public void authenticate() */
    /**
     * 用户权限验证
     *
     * @access public void
     * @return void
     */
    public function authenticate($appname, $username, $machine, $nodename)
    {
        $perms  = Aleafs_Sem_User::getPermission($username, $appname);
        if (empty($perms)) {
            $trials = Aleafs_Lib_Configer::instance('trial_user');
            $days   = $trials->get(sprintf('%s.%s', $appname, $username), 0);
            if (empty($days)) {
                $this->errno    = self::E_NOT_TRIAL;
                return;
            }

            $pmid   = Aleafs_Sem_User::addPermission($username, $appname, array(
                'pm_stat'   => Aleafs_Sem_User::STAT_NORMAL,
                'pm_type'   => Aleafs_Sem_User::PERM_TRIAL,
                'pm_func'   => 'BASE',
                'begdate'   => date('Y-m-d'),
                'enddate'   => date('Y-m-d', time() + 86400 * ($days - 1)),
            ));
            if (empty($pmid)) {
                $this->errno    = self::E_SYS_ERROR;
                return;
            }
            $perms  = Aleafs_Sem_User::getPermission($username, $appname);
        }

        $this->permissions      = $perms;
        $this->authenticated	= true;
    }
    /* }}} */

    /* {{{ public void checkAuth() */
    /**
     * 检查认证结果
     *
     * @access public
     * @see http://www.laruence.com/2010/03/26/1365.html
     * @return void
     */
    public function checkAuth()
    {
        if (empty($this->authenticated)) {
        }
    }
    /* }}} */

}

