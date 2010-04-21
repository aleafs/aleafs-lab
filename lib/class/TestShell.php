<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Unit Test Case Class												|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;

require_once(__DIR__ . '/autoload.php');
require_once('PHPUnit/Framework/TestCase.php');

AutoLoad::init();
AutoLoad::register('aleafs\\lib', __DIR__);
AutoLoad::register('aleafs\\lib\\db', __DIR__ . '/db/');

date_default_timezone_set('Asia/Shanghai');

class LibTestShell extends \PHPUnit_Framework_TestCase
{

}

