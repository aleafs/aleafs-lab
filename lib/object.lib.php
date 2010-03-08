<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | HA_Lib 对象基类						    							|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$

if (!defined('HALIB_OBJECT')) {
    define('HALIB_OBJECT',  true);
}

abstract class HA_Object
{

    /* {{{ 成员变量 */

    /**
     * @错误号
     */
    private $_intLastErrNo;

    /**
     * @错误描述
     */
    private $_strLastError;

    /**
     * @进程号
     */
    protected static $_intPid;

    /**
     * @日志对象
     */
    protected static $_objLog;

    /**
     * @LIB库路径
     */
    protected static $_libDir;

    /* }}} */

    /* {{{ public static String  getclass() */
    /**
     * 获取叶子类的类名
     *
     * @access public static
     * @param  String $strFat (default null)
     * @return String or Boolean false
     */
    public static function getclass($strFat = null)
    {
        $strFat = is_string($strFat) && class_exists($strFat) ? $strFat : null;
        foreach (array_reverse(debug_backtrace()) AS $mixOpt) {
            if (!isset($mixOpt['class']) || empty($mixOpt['class'])) {
                continue;
            }

            if ($strFat === null
                || strcasecmp($mixOpt['class'], $strFat) == 0
                || is_subclass_of($mixOpt['class'], $strFat)
            ) {
                return $mixOpt['class'];
            }
        }

        return get_class($this);
    }
    /* }}} */

    /* {{{ public static String  realpath() */
    /**
     * 返回一个绝对路径名
     *
     * @access public static
     * @param  String $strVal
     * @return String or Boolean false
     */
    public static function realpath($strVal)
    {
        $strVal = str_replace('\\', '/', trim($strVal));
        $arrTmp = array_filter(array_map('trim', explode('/', $strVal)), 'strlen');
        $arrRet = array();

        $strRet = strncmp($strVal, '/', 1) == 0 ? '/' : '';
        foreach ($arrTmp AS $strTmp) {
            if ($strTmp == '.') {
                continue;
            }

            if ($strTmp == '..') {
                array_pop($arrRet);
            } else {
                $arrRet[] = $strTmp;
            }
        }
        $strRet.= implode('/', $arrRet);

        return $strRet;
    }
    /* }}} */

    /* {{{ public static Boolean import() */
    /**
     * 加载文件
     *
     * @access public static
     * @param  String $strDir
     * @param  String $strExt
     * @return Boolean true or false
     */
    public static function import($strDir, $strExt = '.php')
    {
        $strDir = trim($strDir);
        $strExt = trim($strExt);

        if (is_file($strDir)) {
            if (strripos($strDir, $strExt) !== (strlen($strDir) - strlen($strExt))) {
                return false;
            }
            require_once($strDir);
            return true;
        }

        if (is_dir($strDir) && ($arrVal = glob(sprintf('%s/%s%s', $strDir, '*', $strExt)))) {
            foreach ($arrVal AS $strVal) {
                require_once($strVal);
            }

            return true;
        }

        return false;
    }
    /* }}} */

    /* {{{ public static Boolean unlink() */
    /**
     * 清理过期文件
     *
     * @access protected
     * @param  String  $strDir
     * @param  Integer $intDay
     * @return Boolean true or false
     */
    public static function unlink($strDir, $intDay)
    {
        $strDir = self::realpath($strDir);
        if (is_file($strDir)) {
            return unlink($strDir);
        }

        if (!is_dir($strDir)) {
            return true;
        }

        $intDay = max((int)$intDay, 1);
        $intExp = strtotime(sprintf('-%d day', $intDay));
        $strExp = sprintf('/(%s|%s)/', date('Ymd', $intExp), date('Y\-m\-d', $intExp));

        if (false === ($arrDir = glob($strDir . '/' . '*'))) {
            return false;
        }

        foreach ($arrDir AS $strTmp) {
            if (is_dir($strTmp)) {
                self::unlink($strTmp, $intDay);
                continue;
            }

            if (filemtime($strTmp) >= $intExp) {
                continue;
            }

            if (!preg_match('/\d{4}\-?\d{2}\-?\d{2}/', $strTmp, $arrRes)) {
                continue;
            }

            unlink($strTmp);
            $arrLog = array(
                'days'  => $intDay,
                'file'  => $strTmp,
            );
        }
        clearstatcache();

        return true;
    }
    /* }}} */

    /* {{{ public static Mixture htmlescape() */
    /**
     * 对输出进行HTML转义
     *
     * @access public static
     * @param  Mixture $mixVal
     * @return Mixture
     */
    public static function htmlescape($mixVal)
    {
        if (is_string($mixVal)) {
            return htmlspecialchars($mixVal);
        }

        if (is_array($mixVal)) {
            $mixRet = array();
            foreach ($mixVal AS $k => $v) {
                $mixRet[self::htmlescape($k)] = self::htmlescape($v);
            }

            return $mixRet;
        }

        return $mixVal;
    }
    /* }}} */

    /* {{{ public static String  userip() */
    /**
     * 获取用户IP
     *
     * @access public static
     * @param  Boolean $bolInt (default false)
     * @return String
     */
    public static function userip($bolInt = false)
    {
        $arrKey = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        );

        $strRet = 'unknown';
        foreach ($arrKey AS $strKey) {
            $strVal = trim($_SERVER[trim($strKey)]);
            if (!empty($strVal) && strcasecmp($strVal, 'unknown') != 0) {
                $strRet = $strVal;
                break;
            }
        }

        return $bolInt ? sprintf('%u', ip2long($strRet)) : $strRet;
    }
    /* }}} */

    /* {{{ protected Object  _init()  */
    /**
     * 对象初始方法
     *
     * @access protected
     * @return Object $this
     */
    final protected function _init()
    {
        $this->_intLastErrNo = 0;
        $this->_strLastError = '';
        if (is_null(self::$_intPid)) {
            self::$_intPid = getmypid();
        }
        if (is_null(self::$_libDir)) {
            self::$_libDir = dirname(__FILE__);
        }
        self::import(self::$_libDir . '/filelog.lib.php');

        return $this;
    }
    /* }}} */

    /* {{{ protected static Boolean _notice() */
    /**
     * NOTICE日志
     *
     * @access protected static
     * @return Boolean true or false
     */
    protected static function _notice()
    {
        $arrArg	= func_get_args();
        return self::_log($arrArg, HA_FileLog::LOG_LEVEL_NOTICE);
    }
    /* }}} */

    /* {{{ protected Boolean _error() */
    /**
     * 抛出一个错误信息
     *
     * @access protected
     * @param  Integer $intErrNo 错误号
     * @param  String  $strError 错误描述
     * @param  Boolean $bolHalt  是否终止 default false
     * @return Boolean true or false
     */
    protected function _error($intErrNo, $strError, $bolHalt = false)
    {
        $this->_intLastErrNo = intval($intErrNo);
        $this->_strLastError = trim($strError);

        $arrLog = array(
            'error: class=%s,code=%d,message=%s',
            get_class($this), $this->_intLastErrNo, $this->_strLastError
        );

        if ($bolHalt === true) {
            if (method_exists($this, '_on_error_occurred')) {
                $this->_on_error_occurred();
            } else {
                throw new Exception($this->_strLastError, $this->_intLastErrNo);
            }
            self::_log($arrLog, HA_FileLog::LOG_LEVEL_FATAL);
            exit;
        }
        return self::_log($arrLog, HA_FileLog::LOG_LEVEL_ERROR);
    }
    /* }}} */

    /* {{{ private static Boolean _log() */
    /**
     * 写入日志记录
     *
     * @access private static
     * @return Boolean true  or false
     */
    private static function _log($arrArg, $intLvl)
    {
        if (is_null(self::$_objLog)) {
            self::$_objLog = HA_FileLog::instance();
        }
        array_unshift($arrArg, $intLvl);

        return call_user_func_array(array(&self::$_objLog, 'write'), $arrArg);
    }
    /* }}} */

}

