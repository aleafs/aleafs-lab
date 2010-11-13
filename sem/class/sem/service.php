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

    /* {{{ public void AuthHeader() */
    /**
     * 验证头信息
     *
     * @access public
     * @return void
     */
    public function AuthHeader($header)
    {
        $this->authenticated    = false;
        $this->authenticate(
            strtolower(trim($header->appname)),
            trim($header->username),
            trim($header->machine),
            trim($header->nodename)
        );
    }
    /* }}} */

    /* {{{ protected void setSoapHeader() */
    /**
     * 设置返回的SOAP头信息
     *
     * @access protected
     * @return void
     */
    protected function setSoapHeader($path = '')
    {
        $server = &Aleafs_Lib_Context::get('soap.server');
        if (empty($server)) {
            return;
        }

        $server->addSoapHeader(new SoapHeader(
            sprintf('%s/%s', Aleafs_Lib_Context::get('webroot'), trim($path, '/')),
            'ResHeader', 
            array(
                'status'        => $this->errno,
                'description'   => self::error($this->errno),
            )
        ));
    }
    /* }}} */

    /* {{{ private void authenticate() */
    /**
     * 用户权限验证
     *
     * @access public void
     * @return void
     */
    private function authenticate($appname, $appuser, $machine, $nodename)
    {
        $perms  = Aleafs_Sem_User::getPermission($appuser, $appname);
        if (empty($perms)) {
            $trials = Aleafs_Lib_Configer::instance('trial_user');
            $days   = $trials->get(sprintf('%s.%s', $appname, $appuser), 0);
            if (empty($days)) {
                $this->errno    = self::E_NOT_TRIAL;
                return;
            }

            $userid = Aleafs_Sem_User::initUser(
                Aleafs_Sem_User::username($appuser, $appname),
                array(
                    'usertype'  => Aleafs_Sem_User::TYPE_CLIENT,
                    'userstat'  => Aleafs_Sem_User::STAT_NORMAL,
                )
            );
            $pmid   = Aleafs_Sem_User::addPermission($appuser, $appname, array(
                'userid'    => $userid,
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
            $perms  = Aleafs_Sem_User::getPermission($appuser, $appname);
        }

        $this->permissions      = $perms;
        $this->authenticated	= true;
    }
    /* }}} */

    /* {{{ private static string error() */
    /**
     * 获取错误描述
     *
     * @access private static
     * @return string
     */
    private static function error($errno)
    {
        if (isset(self::$errorMessage[$errno])) {
            return self::$errorMessage[$errno];
        }

        return 'unknown';
    }
    /* }}} */

}

