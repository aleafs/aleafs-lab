<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 异步的并发HTTP请求类  					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: spider.php 1 2010-04-15 16:28:45Z zhangxc83 $

namespace Aleafs\Lib;

class Spider
{

	/* {{{ 静态常量 */

	const CURLM_MAX_THREADS	= 10;

	/* }}} */

	/* {{{ 成员变量 */

	private $handle		= null;

	private $options	= array();

	private $request	= array();

	private $results	= array();

	private $running;

	private $status;

	/* }}} */

	/* {{{ public void __construct() */
	/**
	 * 构造函数
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->handle	= curl_multi_init();
		$this->options	= array(
			'code'  => CURLINFO_HTTP_CODE,
			'time'  => CURLINFO_TOTAL_TIME,
			'length'=> CURLINFO_CONTENT_LENGTH_DOWNLOAD,
			'type'  => CURLINFO_CONTENT_TYPE,
			'url'   => CURLINFO_EFFECTIVE_URL,
		);
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
		foreach ($this->request AS $curl) {
			if (!$curl) {
				continue;
			}

			curl_multi_remove_handle($curl);
			curl_close($curl);
		}

		if ($this->handle) {
			curl_multi_close($this->handle);
			$this->handle	= null;
		}
    }
    /* }}} */

	/* {{{ private void unBlockRun() */
	/**
	 * 非阻塞模式运行请求
	 *
	 * @access private
	 * @param String $method
	 * @param String $path
	 * @param Array $data
	 * @return void
	 */
	private function unBlockRun($method, $path, $data = null)
	{
		if (empty($this->mcurl)) {
			$this->mcurl = curl_multi_init();
		}

		$index  = 0;
		$this->result   = array();
		while (false !== ($host = $this->fetchHost($index++))) {
			$curl = $this->getCurl($method, $this->getUrl($host, $path), $data);
			$code = curl_multi_add_handle($this->mcurl, $curl);

			if ($code == CURLM_CALL_MULTI_PERFORM || $code == CURLM_OK) {
				do {
					$this->status = curl_multi_exec($this->mcurl, $this->isrun);
				} while ($this->status == CURLM_CALL_MULTI_PERFORM);
			}

			$this->pools[(string)$curl] = $host;
			if (count($this->pools) >= self::CURLM_MAX_THREADS) {
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
			if (curl_multi_select($this->mcurl, 0) > 0) {
				do {
					$this->status = curl_multi_exec($this->mcurl, $this->isrun);
					usleep($innerSleep);
					$innerSleep *= 2;
				} while ($this->status == CURLM_CALL_MULTI_PERFORM);
				$innerSleep = 0;
			}

			while ($done = curl_multi_info_read($this->mcurl)) {
				$handle = &$done['handle'];
				$index  = (string)$handle;
				$this->result[$this->pools[$index]] = curl_multi_getcontent($handle);
				curl_multi_remove_handle($this->mcurl, $handle);
				curl_close($handle);
				unset($this->pools[$index]);
			}
		}
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
		curl_setopt($curl, CURLOPT_USERAGENT,       'Taobao Edp Glider 2.0');

		$method = strtoupper(trim($method));
		switch ($method) {
		case 'POST':
			curl_setopt(CURLOPT_POST,   true);
			break;

		case 'GET':
			break;

		default:
			curl_setopt(CURLOPT_CUSTOMREQUEST,  $method);
			break;
		}
		if (!empty($data)) {
			curl_setopt(CURLOPT_POSTFIELDS, http_build_query($data));
		}

		return $curl;
	}
	/* }}} */

}

