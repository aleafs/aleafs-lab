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

class Sqlite extends Db
{

    private $file;

    private $mode;

	public function __construct($file, $mode = 0666)
	{
		$this->file = $file;
        $this->mode = $mode;

        ini_set('sqlite.assoc_case', 2);    /* LOWER CASE*/
	}

    public function getAll()
    {
		if (empty($this->datares)) {
			return null;
        }

        return sqlite_fetch_all($this->datares, SQLITE_ASSOC);
    }

	protected function _connect()
	{
        $this->link = sqlite_open($this->file, $this->mode, $error);
    }

    protected function _disconnect($link)
    {
        return sqlite_close($link);
    }

    protected function _query($sql)
    {
        return sqlite_query($this->link, $sql);
    }

    protected function _begin()
    {
    }

    protected function _commit()
    {
    }

    protected function _rollback()
    {
    }

    protected function _fetch($res)
    {
        return sqlite_fetch_array($res, SQLITE_ASSOC);
    }

    protected function _error()
    {
        $code = sqlite_last_error($this->link);
        if (!$code) {
            return false;
        }

        return array(
            'code'    => $code,
            'message' => sqlite_error_string($code),
        );
    }

    protected static function _escape($string)
    {
        return sqlite_escape_string($string);
    }

}

