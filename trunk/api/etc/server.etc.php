<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Զ�̷�����ע��															|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$

/**
 * @ע�����ݿ�
 */
if (class_exists('HA_Dbpool')) {
    HA_Dbpool::register(
        'default',
        'mysql',
        array(
            )
    );
}

