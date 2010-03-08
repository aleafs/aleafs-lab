<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 远程服务器注册															|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$

/**
 * @注册数据库
 */
if (class_exists('HA_Dbpool')) {
    HA_Dbpool::register(
        'default',
        'mysql',
        array(
            )
    );
}

