<?php
namespace Aleafs\Lib\Render;

class Html
{

	private static $tplDir	= null;

	private static $objDir	= null;

	private static $expire	= 0;

	private $data	= array();

	public function __construct($tpl, $obj, $expire = 0)
	{
		self::$tplDir	= trim($tpl);
		self::$objDir	= trim($obj);
		self::$expire	= (int)$expire;

		$this->removeAllParams();
	}

	public function register($key, $val)
	{
		$this->data[trim($key)] = $val;
	}

	public function unregister($key)
	{
		unset($this->data[trim($key)]));
	}

	public function removeAllParams()
	{
		$this->data	= array();
	}

	public function render($tplName)
	{
	}

	private function compile()
	{
	}

}

