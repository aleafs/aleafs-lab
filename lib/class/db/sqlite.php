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
// $Id: sqlite.php 18 2010-04-13 15:40:37Z zhangxc83 $
//

namespace Aleafs\Lib\Db;

use \Aleafs\Lib\Database;

class Sqlite extends Database
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
        $this->link = sqlite_open($this->file, $this>mode, $error);
        if (!$this->link) {
            $this->link = null;
            return false;
        }

        return true;
    }

    protected function _disconnect()
    {
        if (!empty($this->link)) {
            sqlite_close($this->link);
        }
    }

    protected function _query($sql)
    {
        return sqlite_query($this->link, $sql);
    }

    protected function _begin()
    {
        return $this->query('BEGIN');
    }

    protected function _commit()
    {
        return $this->query('COMMIT');
    }

    protected function _free()
    {
        $this->datares  = null;
    }

    protected function _rollback()
    {
        return $this->query('ROLLBACK');
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

    protected function _lastId()
    {
        return sqlite_last_insert_rowid($this->link);
    }

    protected static function _escape($string)
    {
        return sqlite_escape_string($string);
    }

    protected function _numRows()
    {
        return sqlite_num_rows($this->datares);
    }

    protected function _affectedRows()
    {
        return sqlite_changes($this->link);
    }

}
