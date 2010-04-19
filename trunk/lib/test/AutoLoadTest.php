<?php
namespace Aleafs\Lib;

require_once(__DIR__ . '/../class/autoload.php');
require_once 'PHPUnit/Framework/TestCase.php';

class AutoLoadTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
	{
        AutoLoad::init();
		AutoLoad::removeAllRules();
		AutoLoad::register('aleafs\\lib', __DIR__ . '/../class');
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function test_should_class_loader_worked_fine()
    {
    }

    public function test_should_throw_file_not_found_when_cant_find_class_file()
    {
    }

    public function test_should_throw_class_not_found_when_rule_not_defined()
    {
    }

    public function test_should_throw_class_not_found_when_class_not_in_file()
    {
    }
}

