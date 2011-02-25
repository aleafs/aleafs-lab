<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 基本服务处理类	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: access.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Service_Access extends Aleafs_Sem_Service
{
	//各操作所需权限，一个操作可以需要多个权限
	private static $NeedPri = array(
		"permission" => array(array('pm_stat'   => Aleafs_Sem_User::STAT_NORMAL,
                							 'pm_func'   => 'BASE')),
		"version" =>  array(array('pm_stat'   => Aleafs_Sem_User::STAT_NORMAL,
                							 'pm_func'   => 'BASE')),
	);
	
	
    /* {{{ public Mixture permission() */
    /**
     * 获取用户权限列表
     *
     * @access public
     * @return Mixture
     */
    public function permission()
    {
        if (!$this->hasPrivileges(self::$NeedPri, "permission")) {
        	$this->errno = self::E_ACCESS_DENIED;
            $this->setSoapHeader('soap/access');
            return array(array(
            	'status' => '',
                'unit'      => '',
                'type'      => 0,
                'begdate'   => '0000-00-00',
                'enddate'   => '0000-00-00',
                'balance'   => 0,
            ));
        }

        $this->setSoapHeader('soap/access');
        
        $perms  = array();
        foreach ($this->permissions AS $row) {
            $perms[] = array(
            	'status' => $row['pm_stat'],
                'unit'      => $row['pm_func'],
                'type'      => (int)$row['pm_type'],
                'begdate'   => $row['begdate'],
                'enddate'   => $row['enddate'],
                'balance'   => (int)Aleafs_Sem_Comfunc::balance($row['enddate'], date('Y-m-d')),
            );
        }

        return $perms;
    }
    /* }}} */

    /* {{{ public Mixture version() */
    /**
     * 得到软件的当前版本
     *
     * @return array
     */
    public function version()
    {
        if (!$this->hasPrivileges(self::$NeedPri, "version")) {
        	$this->errno = self::E_ACCESS_DENIED;
            $this->setSoapHeader('soap/access');
        	return array("software" => "", "version" => "");
        }

        $this->setSoapHeader('soap/access');
        
        $arrRet = array("software" => "windows");

        $config = Aleafs_Lib_Configer::instance('default');
        $arrRet['version'] = $config->get("software.version");

        return $arrRet;
    }

    /* }}} */

    /* {{{ public Mixture heartbeat() */
    /**
     * 心跳检测接口
     *
     * @access public
     * @return Mixture
     */
    public function heartbeat($ua)
    {
        $this->setSoapHeader('soap/access');
        return array(
            'feedback'  => 'access/heartbeat',
            'function'  => $ua->software,
            'args'      => $ua->version,
        );
    }
    /* }}} */

}

