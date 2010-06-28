<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 支持异步并发的HTTP类					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2003 - 2010 Taobao.com. All Rights Reserved				|
// +------------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+

namespace Aleafs\Lib;

class Spider
{

    /* {{{ 静态常量 */

    const MAX_THREADS	= 10;

    /* }}} */

    /* {{{ 静态变量 */

    private static $default = array(
        'log_url'   => null,
        'retry'     => array(300, 500),
        'timeout'   => array(
            'conn'  => 1,
            'write' => 2,
            'read'  => 5,
        ),
    );

    /* }}} */

    /* {{{ 成员变量 */

    private $option = array();

    private $handle = null;

    private $offset = 0;

    private $hosts  = array();

    private $pools  = array();

    private $result = array();

    private $status;

    private $isrun;

    private $log;


    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param Array $option
     * @return void
     */
    public function __construct($option = null)
    {
        $this->option = array_merge(self::$default, (array)$option);
        if (!empty($this->option['log_url'])) {
            $this->log  = new Log($this->option['log_url']);
        }
        $this->handle   = curl_multi_init();

        $this->pools    = array();
        $this->hosts    = array();
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
        if (!empty($this->handle)) {
            curl_multi_close($this->handle);
            $this->handle    = null;
        }
    }
    /* }}} */

    /* {{{ public void register() */
    /**
     * 注册一个URL
     *
     * @access public
     * @param string $url
     * @return void
     */
    public function register($url, $method = 'GET', $data = null)
    {
        $this->hosts[] = array(
            'url'   => trim($url),
            'type'  => strtoupper($method),
            'data'  => $data,
        );
        $this->offset   = count($this->hosts) - 1;
        $this->smartRun();
    }
    /* }}} */

    /* {{{ public void cleanAllData() */
    /**
     * 清理所有的URL
     *
     * @access public
     * @return void
     */
    public function cleanAllData()
    {
        $this->hosts    = array();
        $this->result   = array();
        $this->offset   = 0;
    }
    /* }}} */

    /* {{{ public String getResult() */
    /**
     * 获取执行结果
     *
     * @access public
     * @param String $key
     * @return String
     */
    public function getResult($key)
    {
        if (!isset($this->result[$key])) {
            $this->storeResult();
        }

        if (isset($this->result[$key])) {
            return $this->result[$key];
        }

        return null;
    }
    /* }}} */

    /* {{{ private void smartRun() */
    /**
     * 非阻塞模式运行请求
     *
     * @access private
     * @return void
     */
    private function smartRun()
    {
        while (false !== ($host = $this->fetchUrl($this->offset++))) {
            $curl = $this->getCurl($host['type'], $host['url'], $host['data']);
            $code = curl_multi_add_handle($this->handle, $curl);

            if ($code == CURLM_CALL_MULTI_PERFORM || $code == CURLM_OK) {
                do {
                    $this->status = curl_multi_exec($this->handle, $this->isrun);
                } while ($this->status == CURLM_CALL_MULTI_PERFORM);
            }

            $this->pools[strval($curl)] = $host['url'];
            if (count($this->pools) >= self::MAX_THREADS) {
                $this->storeResult();
            }
        }
    }
    /* }}} */

    /* {{{ private void storeResult() */
    /**
     * 存入异步请求返回的结果
     *
     * @access private
     * @return void
     */
    private function storeResult()
    {
        $innerSleep = 1;
        $outerSleep = 1;
        while ($this->isrun && ($this->status == CURLM_OK || $this->status == CURLM_CALL_MULTI_PERFORM)) {
            usleep($outerSleep);
            $outerSleep *= 2;
            if (curl_multi_select($this->handle, 0) > 0) {
                do {
                    $this->status = curl_multi_exec($this->handle, $this->isrun);
                    usleep($innerSleep);
                    $innerSleep *= 2;
                } while ($this->status == CURLM_CALL_MULTI_PERFORM);
                $innerSleep = 0;
            }

            while ($done = curl_multi_info_read($this->handle)) {
                $handle = &$done['handle'];
                $index  = strval($handle);

                if ($done['result'] != CURLE_OK) {
                    if (!empty($this->log)) {
                        $this->log->warning('SPIDER_ERROR', array(
                            'url'   => $this->pools[$index],
                            'code'  => $done['result'],
                            'error' => curl_error($handle),
                        ));
                    }
                } else {
                    $this->result[$this->pools[$index]] = curl_multi_getcontent($handle);
                    if (!empty($this->log)) {
                        $this->log->debug('SPIDER_OK', array(
                            'url'   => $this->pools[$index],
                        ));
                    }
                }

                curl_multi_remove_handle($this->handle, $handle);
                curl_close($handle);
                unset($this->pools[$index]);
            }
        }
    }
    /* }}} */

    /* {{{ private String fetchUrl() */
    /**
     * 获取完整的URL
     *
     * @access private
     * @param Integer $index
     * @return String
     */
    private function fetchUrl($index)
    {
        if (!isset($this->hosts[$index])) {
            return false;
        }

        return $this->hosts[$index];
    }
    /* }}} */

    /* {{{ private Resource getCurl() */
    /**
     * 初始化curl
     *
     * @access private
     * @param String $method
     * @param String $url
     * @param Mixture $data
     * @return Resource $curl
     */
    private function getCurl($method, $url, $data = null)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_FAILONERROR,     true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION,  true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($curl, CURLOPT_BUFFERSIZE,      8192);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,  $this->option['timeout']['conn']);
        curl_setopt($curl, CURLOPT_MAXREDIRS,       3);
        curl_setopt($curl, CURLOPT_TIMEOUT,         (int)(1.2 * array_sum($this->option['timeout'])));
        curl_setopt($curl, CURLOPT_ENCODING,        'gzip,deflate');
        curl_setopt($curl, CURLOPT_USERAGENT,       'Taobao Edp Myfox 1.0');

        $method = strtoupper(trim($method));
        switch ($method) {
        case 'POST':
            curl_setopt($curl, CURLOPT_POST,   true);
            break;

        case 'GET':
            break;

        default:
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST,  $method);
            break;
        }
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        return $curl;
    }
    /* }}} */

}

