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

use \Aleafs\Factory;

class Spider
{

    /* {{{ 静态常量 */

    const MAX_THREADS	= 10;

    const SLOW_SECONDS  = 10;

    const USER_AGENT    = 'Aleafs Spider 1.0';

    /* }}} */

    /* {{{ 成员变量 */

    private $thread = self::MAX_THREADS;

    private $agents = self::USER_AGENT;

    private $handle = null;

    private $offset = 0;

    private $hosts  = array();

    private $pools  = array();

    private $result = array();

    private $errors = array();

    private $status;

    private $isrun;

    private $ini;

    private $log;

    private $slow;

    private $keep_alive = 0;

    private $slow_threshold = self::SLOW_SECONDS;

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
        $this->ini  = Factory::getIni($ini, true);
        if (!empty($this->ini->access_log)) {
            $this->log  = Factory::getLog($this->ini->access_log);
        } else {
            $this->log  = new \Edp\Core\BlackHole\Log('');
        }

        if (!empty($this->ini->slow_log)) {
            $this->slow = Factory::getLog($this->ini->slow_log);
        } else {
            $this->slow = new \Edp\Core\BlackHole\Log('');
        }

        if (!empty($this->ini->max_threads)) {
            $this->thread = (int)$this->ini->max_threads;
        }

        if (!empty($this->ini->user_agent)) {
            $this->agents = trim($this->ini->user_agent);
        }

        if (!empty($this->ini->keep_alive)) {
            $this->keep_alive   = max(0, (int)$this->ini->keep_alive);
        }
        if (!empty($this->ini->slow_second)) {
            $this->slow_threshold  = (int)$this->ini->slow_second;
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
            $this->_getResult();
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
                $this->pools[(string)$curl] = array(
                    'pos'   => $this->offset,
                    'res'   => $curl,
                );
            }

            $this->offset++;
            if (count($this->pools) >= $this->thread) {
                break;
            }
        }

        do {
            $this->status = curl_multi_exec($this->handle, $this->isrun);
        } while ($this->status == CURLM_CALL_MULTI_PERFORM);
    }
    /* }}} */

    /* {{{ private void _getResult() */
    /**
     * 存入异步请求返回的结果
     *
     * @access private
     * @return void
     */
    private function _getResult()
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
                $this->store($done['handle'], $done['result']);
            }
            $this->smartRun();
        }

        if (!empty($this->pools)) {
            usleep(500);
            foreach ($this->pools AS $pool) {
                $this->store($pool['res']);
            }
        }
    }
    /* }}} */

    /* {{{ private void store() */
    /**
     * 储存某个句柄的结果
     *
     * @access private
     * @param resource $handle
     * @return void
     */
    private function store($handle, $done = CURLE_OK)
    {
        $pinfos = $this->pools[(string)$handle];
        $index  = $pinfos['pos'];

        $notice = array(
            'url'   => $this->hosts[$index]['url'],
            'code'  => curl_getinfo($handle, CURLINFO_HTTP_CODE),
            'errno' => curl_errno($handle),
            'error' => curl_error($handle),
        );

        $data   = curl_multi_getcontent($handle);
        $size   = strlen($data);
        if ($done != CURLE_OK || empty($size)) {
            $this->log->warning('SPIDER_ERROR', $notice);
            $this->errors[$index] = $notice;
        } else {
            $this->result[$index] = $data;
            unset($notice['error'], $notice['errno']);
            $this->log->info('SPIDER_OK', array_merge(
                $notice, array('size' => $size, )
            ));
        }

        if (curl_getinfo($handle, CURLINFO_TOTAL_TIME) >= $this->slow_threshold) {
            $this->slow->warning('HTTP_SLOW', curl_getinfo($handle));
        }

        curl_multi_remove_handle($this->handle, $handle);
        curl_close($handle);
        unset($this->pools[strval($handle)]);
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
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,  $this->ini->timeout['connect']);
        curl_setopt($curl, CURLOPT_MAXREDIRS,       3);
        curl_setopt($curl, CURLOPT_TIMEOUT,         (int)(1.2 * array_sum($this->ini->timeout)));
        curl_setopt($curl, CURLOPT_ENCODING,        'gzip,deflate');
        curl_setopt($curl, CURLOPT_USERAGENT,       $this->agents);
        if ($this->keep_alive) {
            curl_setopt($curl, CURLOPT_HTTPHEADER,  array(
                'Connection: Keep-Alive',
                sprintf('Keep-Alive: %d', $this->keep_alive)
            ));
        }

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
            $data   = http_build_query($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS,  $data);
            $size   = strlen($data);
        } else {
            $size   = 0;
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER,  array(
            'Content-Length: ' . $size
        ));

        return $curl;
    }
    /* }}} */

}

