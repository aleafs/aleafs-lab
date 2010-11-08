<?php

require_once(__DIR__ . '/../class/TestShell.php');

class Aleafs_Lib_AutoLoadTest extends Aleafs_Lib_LibTestShell
{

	protected function setUp()
	{
		parent::setUp();
		Aleafs_Lib_AutoLoad::removeAllRules();
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function test_should_class_loader_worked_fine()
	{
		Aleafs_Lib_Autoload::register('com', __DIR__ . '/autoload/com');
		Aleafs_Lib_Autoload::register('com\\\\aleafs', __DIR__ . '/autoload/com');

		$case1	= new Com_Aleafs_AutoloadTestClass();
		$this->assertEquals(1, Com_Aleafs_AutoloadTestClass::$requireTime, 'Class Load Failed.');

		$case2	= new Com_Aleafs_AutoloadTestClass();
		$this->assertEquals(1, Com_Aleafs_AutoloadTestClass::$requireTime, 'Class Load Duplicate.');
		$this->assertContains(
			strtr(__DIR__ . '/autoload/com/aleafs/autoloadtestclass.php', '\\', '/'),
			strtr($case2->path(), '\\', '/'),
			'Class Load Error Rules.'
		);
	}

	public function test_should_class_loader_by_order_worked_fine()
	{
		Aleafs_Lib_Autoload::register('com', __DIR__ . '/autoload/com');
		Aleafs_Lib_Autoload::register('com\\\\aleafs1', __DIR__ . '/autoload/com', 'com');
		Aleafs_Lib_Autoload::register('com\\\\aleafs2', __DIR__ . '/autoload/com', 'com');
		Aleafs_Lib_Autoload::unregister('com/aleafs2');
		Aleafs_Lib_Autoload::register('com\\\\aleafs', __DIR__ . '/autoload/com', 'com');

		$case = new Com_Aleafs_AutoloadOrderTestClass();
		$this->assertEquals(
			strtr(__DIR__ . '/autoload/com/autoloadordertestclass.php', '\\', '/'),
			strtr($case->path(), '\\', '/'),
			'Class Load by Order Error.'
		);
	}

	public function test_should_throw_file_not_found_when_cant_find_class_file()
	{
		Aleafs_Lib_Autoload::register('com', __DIR__ . '/autoload/com');
		try {
			$case1 = new Com_I_Am_Not_Exists();
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Aleafs_Lib_Exception, 'Exception Type doesn\'t match,');
			$this->assertContains(
				sprintf('File "%s/autoload/com/i/am/not/exists.php', strtr(__DIR__, '\\', '/')),
				strtr($e->getMessage(), '\\', '/'),
				'Exception Message doesn\'t match.'
			);
		}
	}

	public function test_should_throw_class_not_found_when_rule_not_defined()
	{
		Aleafs_Lib_Autoload::register('com', __DIR__ . '/autoload/com');
		try {
			$case1 = new I_Am_Not_Exists();
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Aleafs_Lib_Exception, 'Exception Type doesn\'t match,');
			$this->assertContains(
				'Class "I_Am_Not_Exists" Not Found',
				$e->getMessage(),
				'Exception Message doesn\'t match.'
			);
		}
	}

	public function test_should_throw_class_not_found_when_class_not_in_file()
	{
		Aleafs_Lib_Autoload::register('com', __DIR__ . '/autoload/com');
		try {
			$case1 = new Com_Aleafs_AutoloadTestCaseClassNameNotMatched();
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Aleafs_Lib_Exception, 'Exception Type doesn\'t match,');
			$this->assertTrue(
				(bool)preg_match(
					'/^Class "(.+?)" NOT FOUND IN "(.+?)"/is',
					$e->getMessage()
				), 
				'Exception Message doesn\'t match.'
			);
		}
	}
}

