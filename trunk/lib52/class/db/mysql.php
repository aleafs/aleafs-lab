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

class Aleafs_Lib_Db_Mysql extends Aleafs_Lib_Database
{

    /* {{{ 静态常量 */

    const CACHE_PREFIX  = '#mysql#';

    /* }}} */

    /* {{{ 成员变量 */

    private $conf;

    private $log;

    private $master;

    private $slave;

    private $isMaster   = false;

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct($name)
    {
        $this->conf = Aleafs_Lib_Configer::instance($name);
        $this->log  = Aleafs_Lib_Factory::getLog($this->conf->get('log.url', ''));

        ini_set('mysql.connect_timeout',    (int)$this->conf->get('timeout', 15));

        $this->master   = new Aleafs_Lib_LiveBox(self::CACHE_PREFIX . '/master', 30);
        foreach ((array)$this->conf->get('master', array()) AS $url) {
            $this->master->register($url);
        }

        $this->slave    = new Aleafs_Lib_LiveBox(self::CACHE_PREFIX . '/slave', 180);
        foreach ((array)$this->conf->get('slave', array()) AS $url) {
            $this->slave->register($url);
        }
    }
    /* }}} */

    /* {{{ public Mixture query() */
    /**
     * 执行QUERY
     *
     * @access protected
     * @return Mixture
     */
    public function query($sql, $try = true)
    {
        if (preg_match('/^(INSERT|DELETE|UPDATE|ALTER|CREATE|DROP|LOAD)/is', trim($sql))) {
            $this->_connectToMaster();
        } else {
            $this->_connectToSlave();
        }

        return $this->_query(self::sqlClean($sql), $try);
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
        if (empty($this->link)) {
            $this->_connectToSlave();
        }

        return empty($this->link) ? false : true;
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
    protected function _query($sql, $try = true)
    {
        $ret = mysql_query($sql, $this->link);
        if (false === $ret) {
            if ($try && 2006 == mysql_errno($this->link)) {
                $this->_disconnect();
                return $this->_query($sql, false);
            }
        }

        return $ret;
    }
    /* }}} */

    /* {{{ protected void _begin() */
    protected function _begin()
    {
        $this->_connectToMaster();
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

    /* {{{ private string _escape() */
    private function _escape($string)
    {
        if (empty($this->link)) {
            $this->_connect();
        }

        return mysql_real_escape_string($string, $this->link);
    }
    /* }}} */

    /* {{{ private void _connectToMaster() */
    private function _connectToMaster()
    {
        if (!empty($this->link) && true === $this->isMaster) {
            return;
        }

        $this->_disconnect();
        $this->_realConnect($this->master);
        $this->isMaster = true;
    }
    /* }}} */

    /* {{{ private void _connectToSlave() */
    /**
     * 连接从库
     */
    private function _connectToSlave()
    {
        if (!empty($this->link)) {
            return;
        }

        try {
            $this->_realConnect($this->slave);
        } catch (\Exception $e) {
            $this->_connectToMaster();
        }
    }
    /* }}} */

    /* {{{ private void _realConnect() */
    /**
     * 实际连接DB
     *
     * @access private
     * @return void
     */
    private function _realConnect(&$box)
    {
        $link   = null;
        $func   = $this->conf->get('pconnect', false) ? 'mysql_pconnect' : 'mysql_connect';
        while (empty($link)) {
            $url    = $box->fetch();
            $host   = parse_url($url);
            if (empty($host)) {
                $this->log->warning('MYSQL_CONFIG_ERROR', array('url' => $url));
                $box->setOffline();
                continue;
            }

            $error  = error_reporting();
            error_reporting(E_ERROR | E_PARSE);
            $link   = $func(
                sprintf('%s:%d', $host['host'], empty($host['port']) ? 3306 : $host['port']),
                rawurldecode($host['user']), rawurldecode($host['pass']), true
            );
            error_reporting($error);

            if (empty($link)) {
                $box->setOffline();
                $this->log->warning('MYSQL_CONNECT_ERROR', array(
                    'errno' => mysql_errno(),
                    'error' => mysql_error(),
                ));
            }
        }

        $encode = $this->conf->get('charset', '');
        if (!empty($encode)) {
            if (function_exists('mysql_set_charset')) {
                mysql_set_charset($encode, $link);
            } else {
                mysql_query('SET NAMES ' . $encode, $link);
            }
        }

        $dbname = $this->conf->get('dbname', '');
        if (!empty($dbname)) {
            mysql_select_db($dbname, $link);
        }

        $this->link = $link;
    }
    /* }}} */

}
