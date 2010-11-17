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

    /* {{{ public Mixture permission() */
    /**
     * 获取用户权限列表
     *
     * @access public
     * @return Mixture
     */
    public function permission()
    {
        $this->setSoapHeader('soap/access');
        if (empty($this->authenticated)) {
            return array();
        }

        $perms  = array();
        foreach ($this->permissions AS $row) {
            $perms[] = array(
                'unit'      => $row['pm_func'],
                'type'      => (int)$row['pm_type'],
                'begdate'   => $row['begdate'],
                'enddate'   => $row['enddate'],
                'balance'   => (int)self::balance($row['enddate'], date('Y-m-d')),
            );
        }

        return $perms;
    }
    
    /**
     * 得到软件的当前版本
     *
     * @return array
     */
    public function version()
    {
    	
   		$this->setSoapHeader('soap/access');
        if (empty($this->authenticated)) {
            return array();
        }
        
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

    /* {{{ private static Integer balance() */
    /**
     * 计算授权余额
     *
     * @access private static
     * @return Integer
     */
    private static function balance($end, $beg = null)
    {
        $beg = strtotime(empty($beg) ? date('Y-m-d') : $beg);
        $end = strtotime($end);

        if ($end < $beg) {
            return 0;
        }

        return 1 + (int)($end - $beg) / 86400;
    }
    /* }}} */

}

