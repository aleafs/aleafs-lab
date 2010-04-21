<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 日志类	    														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;

class Log
{

    /* {{{ 静态常量 */

    const LOG_DEBUG     = 1;
    const LOG_NOTICE    = 2;
    const LOG_TRACE     = 4;
    const LOG_WARN      = 8;
    const LOG_ERROR     = 16;
    const LOG_FATAL     = 32;

    /* }}} */

    /* {{{ 静态常量 */

    private static $symbol = array();         /**<  文件名中的通配符      */

    /* }}} */

    /* {{{ 成员变量 */

    private $url    = null;

    private $file   = null;

    private $level  = 0;          /**<  日志级别      */

    private $ioTime = 0;          /**<  磁盘IO次数      */

    private $buffer = '';         /**<  数据缓冲区      */

    private $cache  = 4096;       /**<  最大缓冲量      */

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String $url
     * @return void
     */
    public function __construct($url)
    {
        $this->url  = trim($url);
        $this->file = null;
    }
    /* }}} */

    /* {{{ public void __destruct() */
    /**
     * 析构函数
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $this->flush();
    }
    /* }}} */

    /* {{{ public void debug() */
    /**
     * 写Debug日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function debug($name, $data, $token = null)
    {
        if ($this->level & self::LOG_DEBUG) {
            return $this->insert('DEBUG', $name, $data, $token);
        }
    }
    /* }}} */

    /* {{{ public void notice() */
    /**
     * 写Notice日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function notice($name, $data, $token = null)
    {
        if ($this->level & self::LOG_NOTICE) {
            return $this->insert('NOTICE', $name, $data, $token);
        }
    }
    /* }}} */

    /* {{{ public void trace() */
    /**
     * 写Trace日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function trace($name, $data, $token = null)
    {
        if ($this->level & self::LOG_TRACE) {
            return $this->insert('TRACE', $name, $data, $token);
        }
    }
    /* }}} */

    /* {{{ public void warn() */
    /**
     * 写Warn日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function warn($name, $data, $token = null)
    {
        if ($this->level & self::LOG_WARN) {
            return $this->insert('WARN', $name, $data, $token);
        }
    }
    /* }}} */

    /* {{{ public void error() */
    /**
     * 写Error日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function error($name, $data, $token = null)
    {
        if ($this->level & self::LOG_ERROR) {
            return $this->insert('ERROR', $name, $data, $token);
        }
    }
    /* }}} */

    /* {{{ public void fatal() */
    /**
     * 写Fatal日志
     *
     * @access private
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return void
     */
    public function fatal($name, $data, $token = null)
    {
        if ($this->level & self::LOG_FATAL) {
            return $this->insert('FATAL', $name, $data, $token);
        }
    }
    /* }}} */

    /* {{{ private Boolean insert() */
    /**
     * 写入一行日志
     *
     * @access private
     * @param  String $char : 日志级别
     * @param  String $name
     * @param  String $data
     * @param  String $token
     * @return Boolean true or false
     */
    private function insert($char, $name, $data, $token)
    {
        if (empty($this->file) && !$this->init()) {
            return false;
        }

        $name = empty($name) ? 'UNKOWN' : $name;
        $data = empty($data) ? '-' : $data;
        $this->buffer .= sprintf(
            "%s: [%s] %s %s %s %s %s\n",
            $char, date('Y-m-d\ H:i:s'),
            Context::userip(),
            Context::trackid() ? Context::trackid() : '*',
            strtoupper($name),
            empty($token) ? '-' : $token,
            is_scalar($data) ? $data : json_encode($data)
        );

        if (strlen($this->buffer) >= $this->cache) {
            $this->flush();
        }

        return true;
    }
    /* }}} */

    /* {{{ private Boolean flush() */
    /**
     * 将日志固化在磁盘上
     *
     * @access private
     * @return Boolean true or false
     */
    private function flush()
    {
        if (empty($this->buffer)) {
            return true;
        }

        $err = error_reporting();
        error_reporting($err ^ E_WARNING);

        if (!is_file($this->file)) {
            $dir = dirname($this->file);
            if (!is_dir($dir)) {
                mkdir($dir, 0744, true);
            }
        }
        $len = file_put_contents($this->file, $this->buffer, FILE_APPEND);
        error_reporting($err);

        $max = strlen($this->buffer);
        $this->buffer = substr($this->buffer, (int)$len);
        $this->ioTime++;

        if (false === $len || $len < $max) {
            return false;
        }

        return true;
    }
    /* }}} */

    /* {{{ private Boolean init() */
    /**
     * 初始化日志对象
     *
     * @access private
     * @return Boolean true or false
     */
    private function init()
    {
        $url = parse_url($this->url);
        if (preg_match('/^[^\w]buffer=(\d+)/is', $url['query'], $match)) {
            $this->cache = (int)$match[1];
        }

        $this->file     = $url['path'];
        $this->level    = 0;
        $tmp = array_flip(explode('.', strtolower($url['host'])));
        if (isset($tmp['debug'])) {
            $this->level += self::LOG_DEBUG;
        }
        if (isset($tmp['notice'])) {
            $this->level += self::LOG_NOTICE;
        }
        if (isset($tmp['trace'])) {
            $this->level += self::LOG_TRACE;
        }
        if (isset($tmp['warn'])) {
            $this->level += self::LOG_WARN;
        }
        if (isset($tmp['error'])) {
            $this->level += self::LOG_ERROR;
        }
        if (isset($tmp['fatal'])) {
            $this->level += self::LOG_FATAL;
        }

        if (empty(self::$symbol) && $this->level > 0) {
            self::$symbol = array(
                '{DATE}'  => date('Ymd'),
                '{HOUR}'  => date('H'),
                '{WEEK}'  => date('w'),
            );
        }

        if (!empty(self::$symbol)) {
            $this->file = str_replace(
                array_keys(self::$symbol),
                self::$symbol, $this->file
            );
        }

        return true;
    }
    /* }}} */

}

