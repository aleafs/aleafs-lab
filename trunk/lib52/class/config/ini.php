<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | INI配置解析类														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: ini.php 58 2010-05-05 00:14:58Z zhangxc83 $

class Aleafs_Lib_Configer_Ini
{

    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function parse()
    {
        $var = parse_ini_file($this->url['path'], true);
        $ret = $var;

        return $ret;
    }

}

