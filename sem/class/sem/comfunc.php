<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 基础函数类			    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: user.php 22 2010-04-15 16:28:45Z zhangxc83 $

class Aleafs_Sem_Comfunc
{
	/* {{{ private static Integer balance() */
    /**
     * 计算授权余额
     * @access private static
     * @return Integer
     */
    public static function balance($end, $beg = null)
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
?>