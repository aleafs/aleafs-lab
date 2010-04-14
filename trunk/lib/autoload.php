<?php

class AutoLoad
{

	private static $rules	= array();

	public static function init()
	{
		spl_autoload_register(array(__CLASS__, 'callback'));
	}

	public static function register($name, $dir)
	{
		$dir = realpath($dir);
		if (empty($dir) || !is_dir($dir)) {
			return false;
		}
		self::$rules[self::normalize($name)] = $dir;
		return true;
	}

	public static function unregister($name)
	{
		$name = self::normalize($name);
		if (isset(self::$rules[$name])) {
			unset(self::$rules[$name]);
		}
	}

	public static function callback($class)
	{
		$ordina	= $class;
		$class	= str_replace('\\', '/', preg_replace('/[^\w\\\\]/is', '', $class));
		$index	= strrpos($class, '/');

		if (false === $index) {
			throw new \Exception('dd');
		}

		$path = strtolower(substr($class, 0, $index));
		$name = strtolower(substr($class, $index + 1));
		foreach (self::$rules AS $key => $dir) {
			if (0 !== strpos($path, $key)) {
				continue;
			}

			$file = $dir . substr($path, strlen($key)) . '/' . $name . '.php';
			if (is_file($file)) {
				require $file;
			} else {
				throw new \Exception(sprintf('File "%s" Not Found.', $file));
			}

			if (!class_exists($ordina)) {
				throw new \Exception(sprintf('Class "%s" Not Found in "%s".', $ordina, $file));
			}

			return;
		}

		throw new \Exception(sprintf('Class "%s" Not Found.', $ordina));
	}

	private static function normalize($name)
	{
		$name = preg_replace('/[^\w\\\\]/is', '', $name);
		return strtolower(rtrim(str_replace('\\', '/', $name), '/'));
	}

}

