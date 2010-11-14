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
        $this->setSoapHeader('access/permission');
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
                'balance'   => (int)self::balance($row['begdate'], $row['enddate']),
            );
        }

        return $perms;
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
        $this->setSoapHeader('access/heartbeat');
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
    private static function balance($beg, $end)
    {
        $beg = strtotime($beg);
        $end = strtotime($end);

        if ($end < $beg) {
            return -1;
        }

        return 1 + (int)($end - $beg) / 86400;
    }
    /* }}} */

}

