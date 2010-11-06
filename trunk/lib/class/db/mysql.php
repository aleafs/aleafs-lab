<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | MySQL操作类	    					    							|
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

class Mysql extends Database
{

    /* {{{ 成员变量 */

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct($ini)
    {
    }
    /* }}} */

    /* {{{ protected Boolean _connect() */
    /**
     * 连接数据库
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _connect()
    {
        if (!$this->link) {
            $this->link = null;
            return false;
        }

        return true;
    }
    /* }}} */

    /* {{{ protected void _disconnect() */
    /**
     * 断开连接
     *
     * @access protected
     * @return void
     */
    protected function _disconnect()
    {
        if (!empty($this->link)) {
            mysql_close($this->link);
            $this->link	= null;
        }
    }
    /* }}} */

    /* {{{ protected Mixture _query() */
    /**
     * 执行QUERY
     *
     * @access protected
     * @return Mixture
     */
    protected function _query($sql, $try = true)
    {
        $ret = mysql_query($sql, $this->link);
        if (false === $ret) {
            if ($try && 2006 == mysql_errno($this->link)) {
                $this->_disconnect();
                return $this->query($sql, false);
            }
        }

        return $ret;
    }
    /* }}} */

    /* {{{ protected void _begin() */
    protected function _begin()
    {
        return $this->query('BEGIN');
    }
    /* }}} */

    /* {{{ protected void _commit() */
    protected function _commit()
    {
        return $this->query('COMMIT');
    }
    /* }}} */

    /* {{{ protected void _rollback() */
    protected function _rollback()
    {
        return $this->query('ROLLBACK');
    }
    /* }}} */

    /* {{{ protected void _free() */
    protected function _free()
    {
        if (!empty($this->datares)) {
            mysql_free_result($this->datares);
        }
        $this->datares  = null;
    }
    /* }}} */

    /* {{{ protected void _fetch() */
    protected function _fetch($res)
    {
        return mysql_fetch_assoc($res);
    }
    /* }}} */

    /* {{{ protected Mixture _error() */
    protected function _error()
    {
        if (empty($this->link)) {
            if (empty($this->msg)) {
                return false;
            }

            return array('code' => 1, 'message' => $this->msg);
        }

        $code = mysql_errno($this->link);
        if (empty($code)) {
            return false;
        }

        return array(
            'code'    => $code,
            'message' => mysql_error($code),
        );
    }
    /* }}} */

    /* {{{ protected integer _lastId() */
    protected function _lastId()
    {
        return mysql_insert_id($this->link);
    }
    /* }}} */

    /* {{{ private string _escape() */
    private function _escape($string)
    {
        if (empty($this->link)) {
            $this->_connect();
        }

        return mysql_real_escape_string($string, $this->link);
    }
    /* }}} */

    /* {{{ protected integer _numRows() */
    protected function _numRows()
    {
        return mysql_num_rows($this->datares);
    }
    /* }}} */

    /* {{{ protected integer _affectedRows() */
    protected function _affectedRows()
    {
        return mysql_affected_rows($this->link);
    }
    /* }}} */

}
