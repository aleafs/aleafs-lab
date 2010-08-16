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

    const USER_AGENT    = 'Aleafs Spider 1.0';

    /* }}} */

    /* {{{ 静态变量 */

    private static $default = array(
        'log_url'   => null,
        'timeout'   => array(
            'conn'  => 2,
            'write' => 10,
            'read'  => 40,
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

    private $errors = array();

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
        $this->errors   = array();
        $this->pools    = array();
        $this->offset   = 0;
    }
    /* }}} */

    /* {{{ public Integer register() */
    /**
     * 注册一个URL
     *
     * @access public
     * @param string $url
     * @return Integer
     */
    public function register($url, $method = 'GET', $data = null)
    {
        $url    = trim($url);
        $this->hosts[] = array(
            'url'   => $url,
            'type'  => strtoupper($method),
            'data'  => $data,
        );

        return count($this->hosts) - 1;
    }
    /* }}} */

    /* {{{ public String  getResult() */
    /**
     * 获取执行结果
     *
     * @access public
     * @param String $key
     * @return String
     */
    public function getResult($index)
    {
        if (empty($this->result)) {
            $this->smartRun();
        }

        $check  = true;
        $index  = trim($index);
        while ($check || isset($this->result[$index])) {
            if (isset($this->result[$index])) {
                return $this->result[$index];
            }
            $this->storeResult();
            $check  = false;
        }

        return null;
    }
    /* }}} */

    /* {{{ public Mixture getError() */
    /**
     * 获取错误信息
     *
     * @access public
     * @param String  $key
     * @param Boolean $codeOnly : default false
     * @return Mixture
     */
    public function getError($key, $codeOnly = false)
    {
        if (empty($this->errors[$key])) {
            return null;
        }

        return $codeOnly ? $this->errors[$key]['errno'] : $this->errors[$key];
    }
    /* }}} */

    /* {{{ private void smartRun() */
    /**
     * 非阻塞模式运行
     *
     * @access private
     * @return void
     */
    private function smartRun()
    {
        while (false !== ($host = $this->fetchUrl($this->offset))) {
            $curl = $this->getCurl($host['type'], $host['url'], $host['data']);
            if (0 == curl_multi_add_handle($this->handle, $curl)) {
                $this->pools[strval($curl)] = array(
                    'pos'   => $this->offset,
                    'res'   => $curl,
                );
            }

            $this->offset++;
            if (count($this->pools) >= self::MAX_THREADS) {
                break;
            }
        }

        do {
            $this->status = curl_multi_exec($this->handle, $this->isrun);
        } while ($this->status == CURLM_CALL_MULTI_PERFORM);
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
        $usleep = 1;
        while (!empty($this->pools) && $this->isrun && $this->status == CURLM_OK) {
            if (curl_multi_select($this->handle, 0) < 1) {
                usleep($usleep);
                $usleep *= 2;
                continue;
            }
            $usleep = min(1, (int)($usleep / 2));

            do {
                usleep($usleep);
                $this->status = curl_multi_exec($this->handle, $this->isrun);
            } while ($this->status == CURLM_CALL_MULTI_PERFORM);

            while ($done = curl_multi_info_read($this->handle)) {
                $handle = $done['handle'];
                $pinfos = $this->pools[strval($handle)];
                $index  = $pinfos['pos'];

                $notice = array(
                    'url'   => $this->hosts[$index]['url'],
                    'code'  => curl_getinfo($handle, CURLINFO_HTTP_CODE),
                    'errno' => curl_errno($handle),
                    'error' => curl_error($handle),
                );

                $data   = curl_multi_getcontent($handle);
                $size   = strlen($data);
                if ($done['result'] != CURLE_OK || empty($size)) {
                    $this->log->warning('SPIDER_ERROR', $notice);
                    $this->errors[$index] = $notice;
                } else {
                    $this->result[$index] = $data;
                    unset($notice['error'], $notice['errno']);
                    $this->log->info('SPIDER_OK', array_merge(
                        $notice, array('size' => $size, )
                    ));
                }

                curl_multi_remove_handle($this->handle, $handle);
                curl_close($handle);
                unset($this->pools[strval($handle)]);
            }

            $this->smartRun();
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
        curl_setopt($curl, CURLOPT_USERAGENT,       self::USER_AGENT);

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

