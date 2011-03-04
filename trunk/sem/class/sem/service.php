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

    const E_RESPONSE_OK     = 0;
    const E_NO_AUTHENTICATE = 401;
    const E_ACCESS_DENIED   = 402;
    const E_SYSTEM_ERROR    = 501;

    /* }}} */

    /* {{{ 静态常量 */

    private static $errorMessage    = array(
        self::E_RESPONSE_OK     => '',
        self::E_NO_AUTHENTICATE => '尚未进行授权验证',
        self::E_ACCESS_DENIED   => '授权拒绝',
        self::E_SYSTEM_ERROR    => '系统繁忙，请稍后再试',
    );

    /* }}} */

    /* {{{ 成员变量 */

    protected $authenticated	= false;

    protected $permissions      = array();

    protected $errno    = self::E_NO_AUTHENTICATE;
    
    protected $username = "";
    
    protected $appname = "";

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
    }
    /* }}} */

    /* {{{ public Mixture __get() */
    /**
     * 魔术方法
     *
     * @access public
     * @return Mixture
     */
    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }
    /* }}} */

    /* {{{ public void AuthHeader() */
    /**
     * 验证头信息
     * 
     * machine字段为授权码
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
        $server = Aleafs_Lib_Context::get('soap.server');
        if (empty($server)) {
            return;
        }

        $server->addSoapHeader(new SoapHeader(
            sprintf('%s/%s', Aleafs_Lib_Context::get('webroot'), trim($path, '/')),
            'ResHeader', 
            json_decode(json_encode(array(
                'status'        => $this->errno,
                'description'   => self::error($this->errno),
            )))
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
        $arrPass = Aleafs_Sem_user::getUserInfo($appuser, array('password'));
        if (empty($arrPass) || $arrPass['password'] != $machine)
        {
        	return;
        }
        
    	$perms  = Aleafs_Sem_User::getPermission($appuser, $appname);
        if (empty($perms)) {
            $trials = Aleafs_Lib_Configer::instance('trial');
            $days   = $trials->get(sprintf('%s.%s', $appname, $appuser), 0);
            if (empty($days)) {
                $this->errno    = self::E_ACCESS_DENIED;
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
                $this->errno    = self::E_SYSTEM_ERROR;
                return;
            }
            $perms  = Aleafs_Sem_User::getPermission($appuser, $appname);
        }

        $this->username = $appuser;
        $this->appname = $appname;
        
        $this->errno    = self::E_RESPONSE_OK;
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

/**
	 * 判断一个操作是否有权限进行
	 *
	 * @param String $op
	 * @return bool
	 */
	protected  function hasPrivileges($NeedPri,  $op)
	{
		 //未经过权限获取操作
		 if (empty($this->authenticated)) {
		 	return false;
		 }
		// 不需要权限判断
		if (empty($NeedPri[$op])) {
			return true;
		}
		//判断是否具有所需权限
		foreach ($NeedPri[$op] as $need) {
			$bolOne = false;
			
			foreach ($this->permissions AS $row) {
	            if ($row['pm_func'] == $need['pm_func']  && $row['pm_stat'] == $need['pm_stat'])
	            {
	            	$intBalance = intval(Aleafs_Sem_Comfunc::balance($row['enddate'], date('Y-m-d')));
	            	//有权限过期
	            	if ($intBalance < 1)
	            	{
	            		$this->errno    = self::E_ACCESS_DENIED;
	            		return false;
	            	} 
	            	else 
	            	{
	            		$bolOne = true;
	            		break;
	            	}
	            }
			}
			//有权限未找到
			if (!$bolOne) 
			{
				$this->errno    = self::E_ACCESS_DENIED;
				return false;
			}
            
        }
        return true;	
	}
	
}

