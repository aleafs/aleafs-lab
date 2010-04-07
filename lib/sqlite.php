<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | SQLITE操作类	    					    							|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$
//
//
class Sqlite
{

	private $file;

	private $mode;

	private $res;

	public function __construct($file, $mode = 0666)
	{
		$this->file = $file;
		$this->mode = $mode;
	}

	public function select($tab, $col, $where, $order)
	{
	}

	public function insert($tab, $value)
	{
	}

	public function delete($tab, $where)
	{
	}

	public function update($tab, $col, $where)
	{
	}

	private function open()
	{
		if (empty($this->res)) {
			$this->res = sqlite_open($this->file, $this->mode, $error);
			if (false === $this->res) {
			
			}
		}
	}
}

