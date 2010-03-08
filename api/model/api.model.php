<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | API模型															|
// +--------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@gmail.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

class App_Model_Api extends HA_Single
{

    /* {{{ 常量定义 */

    /**
     * @ API状态
     */
    const API_STATUS_NEW    = 100;
    const API_STATUS_WAIT   = 200;
    const API_STATUS_RUN    = 300;

    /* }}} */

    /* {{{ 静态变量 */

    /**
     * @主表
     */
    private static $_strDb  = 'aleafs_api';

    /**
     * @APP列表
     */
    private static $_arrApp = array(
        1   => array(
            'name'  => 'antispam',                  /**<  名称      */
            'desc'  => 'aleafs antispam',           /**<  描述      */
            'limit' => 1,                           /**<  单用户应用个数      */
            'table' => 'as_sitelist',               /**<  主表名      */
        ),
    );

    /**
     * @列名
     */
    private static $_arrCol;

    /* }}} */

    /* {{{ 成员变量 */

    /**
     * @对象参数
     */
    private $_arrOpt;

    /**
     * @新的参数
     */
    private $_arrNew;

    /**
     * @短路标记
     */
    private $_bolErr;

    /* }}} */

    /* {{{ public Object  instance() */
    /**
     * 获取单例对象的一个实例
     *
     * @access public static
     * @param  Mixture $mixArg
     * @param  String  $strCls (default null)
     * @return Object
     */
    public static function instance($mixArg = '', $strCls = null)
    {
        return parent::instance($mixArg, is_null($strCls) ? __CLASS__ : $strCls);
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 获取对象的某个属性
     *
     * @access public
     * @param  String $strKey
     * @return Mixture
     */
    public function get($strKey)
    {
        if ($this->_bolErr === false) {
            return null;
        }

        if (empty($this->_arrOpt) && $this->_load() !== true) {
            $this->_bolErr = true;
            return null;
        }

        return $this->_arrOpt[strtolower(trim($strKey))];
    }
    /* }}} */

    /* {{{ public Boolean set() */
    /**
     * 设置属性
     *
     * @access public
     * @param  Mixture $arrOpt
     * @param  Boolean $bolNow (default false)
     * @return Boolean true or false
     */
    public function set($arrOpt, $bolNow = false)
    {
        $this->_arrNew = array_merge((array)$this->_arrNew, self::_col_filter($arrOpt));
        if ($bolNow === true) {
            return $this->_update();
        }

        return true;
    }
    /* }}} */

    /* {{{ public Boolean add() */
    /**
     * 添加一个API记录
     *
     * @access public static
     * @param  Mixture $mixCol
     * @return Object or Boolean false
     */
    public static function add($mixCol)
    {
        $arrCol = array(
            'app_id'  => null,
            'userid'  => null,
            'static'  => self::API_STATUS_RUN,
            'exbuget' => -1,
        );

        $objRes = HA_Dbpool::instance('api');
        foreach ($arrCol AS $strKey => $strVal) {
            if (isset($mixCol[$strKey])) {
                $arrCol[$strKey] = $objRes->escape($mixCol[$strKey]);
            }
        }

        $intApp = (int)$arrCol['app_id'];
        $intUid = (int)$arrCol['userid'];
        if (!isset(self::$_arrApp[$intApp])) {
            self::_error(300, sprintf('Undefined appid as "%d"', $intApp));
            return false;
        }

        $arrCol['apicode'] = self::_gen_code($intApp,  $intUid);
        $arrCol['apptime'] = time();
        if (intval($arrCol['static'] / 100) == intval(self::API_STATUS_RUN / 100)) {
            $arrCol['efttime'] = time();
        }

        $strSql = sprintf(
            "INSERT INTO %s.api_account (%s) VALUES ('%s')",
            self::$_strDb, implode(',', array_keys($arrCol)),
            implode("','", array_values($arrCol))
        );
        if ($objRes->query($strSql) === false) {
            self::_error(301, sprintf('Insert api_account error [%s]', $strSql));
            return false;
        }
        self::_notice('Insert api_account ok [%s]', $strSql);

        return self::instance($objRes->lastId());
    }
    /* }}} */

    /* {{{ public Boolean status() */
    /**
     * 判断API账户的状态
     *
     * @access public
     * @param  Integer $status (default null)
     * @return Boolean true or false
     */
    public function status($status = null)
    {
        $statid = (int)$this->get('statid');
        if ($status === null) {
            return $statid;
        }

        if (intval($statid / 100) == intval((int)$status / 100)) {
            return true;
        }

        return false;
    }
    /* }}} */

    /* {{{ public Boolean __destruct() */
    /**
     * 析构函数
     *
     * @access public
     * @return Boolean true or false
     */
    public function __destruct()
    {
        $this->_update();
        return parent::__destruct();
    }
    /* }}} */

    /* {{{ private Boolean _load() */
    /**
     * 加载信息
     *
     * @access private
     * @return Boolean true or false
     */
    private function _load()
    {
        $objRes = HA_Dbpool::instance('api');
        $strSql = sprintf(
            "SELECT * FROM %s.api_account WHERE %s='%s' LIMIT 1",
            self::$_strDb, is_numeric($this->_strIdx) ? 'api_id' : 'apicode',
            $objRes->escape($this->_strIdx)
        );
        $arrOpt = $objRes->fetch($objRes->query($strSql));
        if (!is_array($arrOpt) || empty($arrOpt)) {
            return false;
        }

        $this->_arrOpt = $arrOpt;
        self::$_arrCol = array_keys($arrOpt);
        if (is_numeric($this->_strIdx)) {
            $this->_alias($arrOpt['apicode']);
        } else {
            $this->_alias($arrOpt['api_id']);
        }

        return true;
    }
    /* }}} */

    /* {{{ private Boolean _update() */
    /**
     * 更新数据到DB
     *
     * @access private
     * @return Boolean true or false
     */
    private function _update()
    {
        if (empty($this->_arrNew)) {
            return true;
        }

        $arrExp = array('api_id', 'app_id', 'userid', 'apptime', 'apicode', 'modtime');
        $arrNew = array_diff_key((array)$this->_arrNew, array_flip($arrExp));
        if (empty($arrNew)) {
            return true;
        }

        $intKey = (int)$this->get('api_id');
        if ($intKey < 1) {
            return false;
        }


        $arrCol = array();
        $objRes = HA_Dbpool::instance('api');
        foreach ($arrNew AS $k => $v) {
            if ($v == $this->_arrOpt[$k]) {
                continue;
            }
            $arrCol[] = sprintf("%s='%s'", $k, $objRes->escape($v));
        }
        if (empty($arrCol)) {
            return true;
        }

        $strSql = sprintf(
            'UPDATE %s SET %s,modtime=%d WHERE api_id=%d LIMIT 1',
            implode(',', $arrCol, time(), $intKey)
        );
        if ($objRes->query($strSql) === false) {
            self::_error(411, sprintf('Update api_account error [%s]', $strSql));
            return false;
        }

        $this->_arrNew = null;
        $this->_arrOpt = array_merge($this->_arrOpt, $arrNew);
        self::_notice('Update api_account OK [%s]', $strSql);

        return true;
    }
    /* }}} */

    /* {{{ private Mixture _col_filter() */
    /**
     * 过滤不相关的列
     *
     * @access private static
     * @param  Mixture $mixCol
     * @return Mixture
     */
    private static function _col_filter($mixCol)
    {
        if (empty(self::$_arrCol)) {
            $arrTmp = HA_Dbpool::instance('api')->field(self::$_strDb . '.api_account');
            if (!is_array($arrTmp)) {
                return $mixCol;
            }
            self::$_arrCol = array_keys($arrTmp);
        }

        $arrRet = array();
        $arrKey = array_flip(self::$_arrCol);
        foreach ((array)$mixCol AS $k => $v) {
            if (!isset($arrKey[$k])) {
                continue;
            }
            $arrRet[$k] = $v;
        }

        return $arrRet;
    }
    /* }}} */

    /* {{{ private String  _gen_code() */
    /**
     * 生成新的API代码
     *
     * @access private static
     * @param  Interger $intApp
     * @param  Interger $intUid
     * @return String
     */
    private static function _gen_code($intApp, $intUid)
    {
        return strtoupper(md5(sprintf(
            "%s\t%s\t%s\t%s", $intApp, self::$_intPid, $intUid, uniqid(rand(), true)
        )));
    }
    /* }}} */

}

if (!function_exists('array_diff_key')) {
    function array_diff_key($a, $b) {
        $arrRet = array();
        foreach ((array)$a AS $k => $v) {
            if (isset($b[$k])) {
                continue;
            }
            $arrRet[$k] = $v;
        }

        return $arrRet;
    }
}

