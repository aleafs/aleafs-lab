<?php
class Com_Aleafs_AutoLoadTestClass
{
	public static $requireTime = 0;

	public function path()
	{
		return __FILE__;
	}
}

Com_Aleafs_AutoLoadTestClass::$requireTime++;

