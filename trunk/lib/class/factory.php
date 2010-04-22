<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Factory Class 														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: log.php 33 2010-04-21 15:41:56Z zhangxc83 $

namespace Aleafs\Lib;

class Factory
{

	private static $reg	= array();

	private static $obj	= array();

	public static function register($class, $name, $option)
	{
		self::$reg[self::objIndex($class, $name)] = $option;
	}

	public static function unregister($class, $name)
	{
		$index	= self::objIndex($class, $name);
		if (isset(self::$reg[$index])) {
			if (isset(self::$obj[$index])) {
				unset(self::$obj[$index]);
			}
			unset(self::$reg[$index]);
		}
	}

	public static function &getObject($class, $name)
	{
		$index	= self::objIndex($class, $name);
		if (empty(self::$obj[$index])) {
			if (empty(self::$reg[$index])) {
				throw new Exception(sprintf(
					'Unregistered object name as "%s" for class "%s"',
					$name, $class
				));
			}
			if (!class_exists($class)) {
				AutoLoad::callback($class);
			}
			$ref = new \ReflectionClass($class);
			self::$obj[$index] = $ref->newInstance(self::$reg[$index]);
		}

		return self::$obj[$index];
	}

	public static function removeAllObject()
	{
		self::$reg	= array();
		self::$obj	= array();
	}

	private static function objIndex($class, $name)
	{
		return sprintf('%s\%s', self::normalize($class), self::normalize($name));
	}

	private static function normalize($class)
	{
		$class = preg_replace('/\s+/', '', preg_replace('/[\/\\]+/', '/', $class));
		return strtolower(trim($class, '/'));
	}

}

