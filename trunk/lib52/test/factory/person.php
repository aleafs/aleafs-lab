<?php

class Factory_Person
{

	private $age;

	private $name;

	public function __construct($name, $age)
	{
		$this->name	= trim($name);
		$this->age	= intval($age);
	}

	public function setAge($age)
	{
		$this->age	= intval($age);
	}

	public function __get($key)
	{
		if (!isset($this->$key)) {
			return null;
		}

		return $this->$key;
	}

}
