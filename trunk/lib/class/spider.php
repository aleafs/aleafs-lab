<?php
namespace Edp\Core;

class Spider
{

	private $handle		= null;

	private $options	= array();

	private $request	= array();

	private $results	= array();

	private $running;

	private $status;

	public function __construct($max)
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

	public function __destruct()
	{
		foreach ($this->request AS $curl) {
		
		}
	
	}

	public function register($ch)
	{
		$key = (string)$ch;
		$this->request[$key] = $ch;

		$ret = curl_multi_add_handle($this->handle, $ch);
		if ($ret == CURLM_OK || $ret == CURLM_CALL_MULTI_PERFORM) {
			do {
				$this->status = curl_multi_exec($this->handle, $this->running);
			} while ($this->status == CURLM_CALL_MULTI_PERFORM);
		}

		return $key;
	}

	public function getResult($key)
	{
		if (!is_scalar($key)) {
			return false;
		}

		if (isset($this->results[$key])) {
			return $this->results[$key];
		}

		while ($this->running && (in_array($this->status, array(CURLM_OK, CURLM_CALL_MULTI_PERFORM)))) {
		
		}
	}

}

