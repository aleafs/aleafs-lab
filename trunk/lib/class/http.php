<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Lib\Http.php		        										|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

namespace Aleafs\Lib;

class Http
{

    /* {{{ 静态常量 */

    const HTTP_USER_AGENT   = 'Aleafs Http Agent';

    /* }}} */

    /* {{{ 静态变量 */

    private static $default = array(
        'server'    => array(),
        'prefix'    => '',
        'timeout'   => array(
            'connect'   => 300,
            'write'     => 100,
            'read'      => 500,
        ),
        'retry'     => array(
            'timeout1'  => 10,
            'timeout2'  => 20,
        ),
    );

    /* }}} */

    /* {{{ 成员变量 */

    private $host   = null;       /**<  服务器池     */

    private $header = null;

    private $ini    = null;

    private $curl   = null;

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String $ini
     * @param  String $key (default null)
     * @return void
     */
    public function __construct($ini, $key = null)
    {
        $this->ini  = array_merge_recursive(self::$default, (array)$ini);
        $this->host = new ConnPool(__CLASS__ . '/' . !is_scalar($key) ? md5(json_encode($ini)) : $key);
        foreach ($this->ini['server'] AS $host) {
            $this->host->register($host);
        }
    }
    /* }}} */

    /* {{{ public String  getLastUrl() */
    /**
     * 获取最后一次请求的真实URL
     *
     * @access public
     * @return String
     */
    public function getLastUrl()
    {
        return $this->lastUrl;
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * GET请求
     *
     * @access public
     * @param  String $url
     * @param  Mixture $header (default null)
     * @return Mixture
     */
    public function get($url, $header = null)
    {
        return $this->run('GET', $url, null, $header);
    }
    /* }}} */

    /* {{{ public Mixture post() */
    /**
     * POST请求
     *
     * @access public
     * @param  String $url
     * @param  Mixture $data
     * @param  Mixture $header (default null)
     * @return Mixture
     */
    public function post($url, $data, $header = null)
    {
        if (empty($data)) {
            $data = array(
                'I\'m not exists' => 'fixed',
            );
        }
        return $this->run('POST', $url, $data, $header);
    }
    /* }}} */

    /* {{{ private void init() */
    /**
     * 初始化CURL对象
     *
     * @access private
     * @return void
     */
    private function init()
    {
        $this->curl = curl_init();
        $option = array(
            CURLOPT_FAILONERROR     => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 3,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => true,
            CURLOPT_BUFFERSIZE      => 8192,
            CURLOPT_CONNECTTIMEOUT  => $this->ini['timeout']['connect'],
            CURLOPT_TIMEOUT         => (int)(1.2 * (array_sum($this->ini['timeout']))),
            CURLOPT_ENCODING        => 'gzip,deflate',
            CURLOPT_USERAGENT       => self::HTTP_USER_AGENT,
        );
        curl_setopt_array($this->curl, $option);
    }
    /* }}} */

    /* {{{ private String run() */
    /**
     * 运行HTTP请求
     *
     * @access private
     * @param  String $method
     * @param  String $url
     * @param  Mixture $data
     * @param  Mixture $header
     * @return String
     */
    private function run($method, $url, $data = null, $header = null)
    {
        if (empty($this->curl)) {
            $this->init();
        }

        switch (trim($method)) {
        case 'POST':
            curl_setopt($this->curl, CURLOPT_POST, true);
            break;
        default:
            break;
        }

        if (!empty($data)) {
            curl_setopt(
                $this->curl, CURLOPT_POSTFIELDS,
                is_scalar($data) ? $data : http_build_query($data)
            );
        }

        $retry = array_unshift($this->ini['retry'], 0);
        foreach ($retry AS $time) {
            $this->lastUrl = $this->fixUrl($url);
            curl_setopt($this->curl, CURLOPT_URL, $this->lastUrl);
            if (false !== ($ret = curl_exec($this->curl))) {
                return $this->split($ret);
            }
            usleep($time * 1000);
        }

        throw new Exception(sprintf(
            'Http Error : [%s] %s',
            curl_errno($this->curl),
            curl_error($this->curl)
        ));
    }
    /* }}} */

    /* {{{ private String fixUrl() */
    /**
     * 补全完整的URL
     *
     * @access private
     * @param  String $url
     * @return String
     */
    private function fixUrl($url)
    {
        return sprintf(
            'http://%s/%s/%s',
            $this->host->getHost(),
            trim($this->ini['prefix'], '/'),
            ltrim($url, '/')
        );
    }
    /* }}} */

    /* {{{ private String split() */
    /**
     * 切割HTTP返回的字符串
     *
     * @access private
     * @param  String $ret
     * @return String
     */
    private function split($ret)
    {
        list($this->header, $body) = explode("\r\n\r\n", trim($ret));
        return $body;
    }
    /* }}} */

}

