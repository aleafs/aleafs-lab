<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+
//
// $Id: example.php 18 2010-04-13 15:40:37Z zhangxc83 $
//

namespace Myfox\App\Task;

class Example extends \Myfox\App\Task
{

	public $counter	= 0;

	public function execute()
	{
		$this->counter++;
		return self::SUCC;
	}

	public function wait()
	{
		$this->setError('None sense for wait');
		return self::FAIL;
	}

}

