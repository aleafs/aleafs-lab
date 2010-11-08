<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 异常处理类	    													|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: session.php 2010-04-19 13:54:32 pengchun Exp $

class Aleafs_Lib_Exception extends Exception
{

    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return sprintf('%s : [%s] : %s', __CLASS__, $this->code, $this->message);
    }

}

