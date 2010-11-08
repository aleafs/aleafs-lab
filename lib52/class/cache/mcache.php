<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Mcache缓存类	        											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: mcache.php 63 2010-05-12 07:40:08Z zhangxc83 $

class Aleafs_Lib_Cache_Mcache
{

    /* {{{ 静态变量 */

    /**
     * @默认配置
     */
    private static $default = array(
        'logurl'    => null,
        'logtime'   => false,
        'prefix'    => '',
        'expire'    => 3600,
        'server'    => array(),
        'timeout'   => array(
            'connect'   => 300,
            'write'     => 100,
            'read'      => 500,
        ),
    );

    /* }}} */

    /* {{{ 成员变量 */

    private $ini;

    private $log;

    private $obj;

    private $cas    = array();

    private $timer  = null;

    private $bufferWrite    = true;

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  Array $ini
     * @return void
     */
    public function __construct(Array $ini)
    {
        $this->ini  = array_merge(self::$default, (array)$ini);
        if (!empty($this->ini['logurl'])) {
            $this->log  = Aleafs_Lib_Factory::getLog($this->ini['logurl']);
        }

        $this->initMcache();
    }
    /* }}} */

    /* {{{ public void setBufferWrite() */
    /**
     * 设置是否缓冲写入
     *
     * @access public
     * @param  Boolean $buffer
     * @return void
     */
    public function setBufferWrite($buffer)
    {
        $this->bufferWrite  = (bool)$buffer;
        if (!empty($this->obj)) {
            $this->obj->setOption(
                \Memcached::OPT_BUFFER_WRITES,
                $this->bufferWrite
            );
        }
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 取出数据
     *
     * @access public
     * @param  String $key
     * @return Mixture
     */
    public function get($key)
    {
        if (!is_array($key)) {
            $key = (string)$key;
            if (!isset($this->cas[$key])) {
                $this->cas[$key] = null;
            }

            $this->beginTimer();
            $ret = $this->obj->get($key, null, $this->cas[$key]);
            $this->writeLog('GET', $key, $this->getElapsed());
            return (false !== $ret) ? $ret : null;
        }

        $cas = null;
        $this->beginTimer();
        $tmp = $this->obj->getMulti($key, $cas, null);
        $this->writeLog('MULTI_GET', $key, $this->getElapsed());

        $ret = array_fill_keys($key, null);
        if (!empty($ret)) {
            $ret = array_merge($ret, (array)$tmp);
        }

        if (!empty($cas)) {
            $this->cas = array_merge($this->cas, $cas);
        }

        return $ret;
    }
    /* }}} */

    /* {{{ public Boolean set() */
    /**
     * 存入数据
     *
     * @access public
     * @param  String $key
     * @param  Mixture $value
     * @param  Integer $expire (default null)
     */
    public function set($key, $value, $expire = null)
    {
        $expire = empty($expire) ? $this->ini['expire'] : $expire;
        $this->beginTimer();
        if (empty($this->cas[$key])) {
            $ret = $this->obj->add($key, $value, $expire);
        } else {
            $ret = $this->obj->cas($this->cas[$key], $key, $value, $expire);
        }
        $this->writeLog('SET', $key, $this->getElapsed());

        if (false != $ret || \Memcached::RES_NOTSTORED == $this->obj->getResultCode()) {
            return true;
        }

        return false;
    }
    /* }}} */

    /* {{{ public Boolean multiSet() */
    /**
     * 以关联数据设置多条数据
     *
     * @access public
     * @param  Array $data
     * @param  Integer $expire
     * @return Boolean true or false
     */
    public function multiSet(Array $data, $expire = null)
    {
        $this->beginTimer();
        $ret = $this->obj->setMulti($data, empty($expire) ? $this->ini['expire'] : $expire);
        $this->writeLog('MULTI_SET', $data, $this->getElapsed());

        return $ret ? true : false;
    }
    /* }}} */

    /* {{{ public Boolean delete() */
    /**
     * 删除缓存数据
     *
     * @access public
     * @param  String $key
     * @return Boolean true or false
     */
    public function delete($key)
    {
        $this->beginTimer();
        $ret = $this->obj->delete($key);
        $this->writeLog('DEL', $key, $this->getElapsed());

        return $ret ? true : false;
    }
    /* }}} */

    /* {{{ public Mixture shell() */
    /**
     * Cache获取shell接口
     *
     * @access public
     * @param  Mixture $callback
     * @param  String  $key
     * @param  Mixture $expire : default null
     * @return Mixture
     */
    public function shell($callback, $key, $expire = null)
    {
        $ret = $this->get($key);
        if (is_array($ret)) {
            $diff   = array_keys(array_diff_key(
                array_flip($key),
                array_filter($ret)      /*  <去掉null值 */
            ));
            if (!empty($diff) && $app = call_user_func($callback, $diff)) {
                $ret = array_merge($ret, (array)$app);
                $this->multiSet($app, $expire);
            }
        } elseif (empty($ret)) {
            $ret = call_user_func($callback, $key);
            if (!empty($ret)) {
                $this->set($key, $ret, $expire);
            }
        }

        return $ret;
    }
    /* }}} */

    /* {{{ public Mixture cleanAllCache() */
    /**
     * 清理所有缓存
     *
     * @access public
     * @return Mixture
     */
    public function cleanAllCache()
    {
        $ret = array();
        foreach ($this->cas AS $key => $cas) {
            $ret[$key] = $this->obj->delete($key);
        }

        return $ret;
    }
    /* }}} */

    /* {{{ private void initMcache() */
    /**
     * 初始化Mcache对象
     *
     * @access private
     * @return void
     */
    private function initMcache()
    {
        if (!empty($this->obj)) {
            return;
        }

        $this->obj  = new \Memcached();
        $this->obj->setOption(\Memcached::OPT_COMPRESSION,   true);
        $this->obj->setOption(\Memcached::OPT_SERIALIZER,    \Memcached::SERIALIZER_IGBINARY);
        $this->obj->setOption(\Memcached::OPT_PREFIX_KEY,    $this->ini['prefix']);
        $this->obj->setOption(\Memcached::OPT_HASH,          \Memcached::HASH_MD5);
        $this->obj->setOption(\Memcached::OPT_DISTRIBUTION,  \Memcached::DISTRIBUTION_CONSISTENT);
        $this->obj->setOption(\Memcached::OPT_BUFFER_WRITES, $this->bufferWrite);

        $this->obj->setOption(\Memcached::OPT_CONNECT_TIMEOUT,   $this->ini['timeout']['connect']);
        $this->obj->setOption(\Memcached::OPT_SEND_TIMEOUT,      $this->ini['timeout']['write']);
        $this->obj->setOption(\Memcached::OPT_POLL_TIMEOUT,      $this->ini['timeout']['read']);

        /**
         * @以下参数请勿修改，可能有bug
         */
        $this->obj->setOption(\Memcached::OPT_BINARY_PROTOCOL,   false);

        foreach ($this->ini['server'] AS $item) {
            list($host, $port) = explode(':', $item);
            $this->obj->addServer($host, $port, 1);
        }
    }
    /* }}} */

    /* {{{ private void beginTimer() */
    /**
     * 开始计时
     *
     * @access private
     * @return void
     */
    private function beginTimer()
    {
        if (!empty($this->ini['logtime'])) {
            $this->timer    = microtime(true);
        }
    }
    /* }}} */

    /* {{{ private String getElapsed() */
    /**
     * 获取计时时间
     *
     * @access private
     * @return String
     */
    private function getElapsed()
    {
        if (empty($this->timer)) {
            return null;
        }

        $elapse = microtime(true) - $this->timer;
        $this->timer    = null;

        return number_format($elapse, 6);
    }
    /* }}} */

    /* {{{ private Boolean writeLog() */
    /**
     * 写入操作日志
     *
     * @access private
     * @param  String $log
     * @param  String $key
     * @param  Number $time : default null
     * @return Boolean true or false
     */
    private function writeLog($log, $key, $time = null)
    {
        if (empty($this->log)) {
            return false;
        }

        $log = 'MCACHE_' . strtoupper(trim($log));
        switch ($err = $this->obj->getResultCode()) {
        case \Memcached::RES_SUCCESS:
            $this->log->debug($log . '_OK', array(
                'prefix'    => $this->ini['prefix'],
                'key'       => $key,
                'elapsed'   => $time,
            ));
            break;

        case \Memcached::RES_NOTFOUND:
        case \Memcached::RES_DATA_EXISTS:
            $this->log->notice($log . '_FAIL', array(
                'prefix'    => $this->ini['prefix'],
                'key'       => $key,
                'code'      => $err,
                'message'   => $this->obj->getResultMessage(),
            ));
            break;

        default:
            $this->log->warn($log . '_ERR', array(
                'prefix'    => $this->ini['prefix'],
                'key'       => $key,
                'code'      => $err,
                'message'   => $this->obj->getResultMessage(),
            ));
            break;
        }

        return true;
    }
    /* }}} */

}

