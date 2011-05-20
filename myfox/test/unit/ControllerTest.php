<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

use \Myfox\App\Controller;

require_once(__DIR__ . '/../../lib/TestShell.php');

class ControllerTest extends \Myfox\Lib\TestShell
{

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_throw_exception_when_action_not_defined() */
    public function test_should_throw_exception_when_action_not_defined()
    {
        $controller = new Controller();
        try {
            $controller->execute('i_am_not_defined', array());
            $this->assertTrue(false, 'Exception should be throwed out.');
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Myfox\Lib\Exception);
            $this->assertContains('Undefined action named as "i_am_not_defined"', $e->getMessage());
        }
    }
    /* }}} */

    /* {{{ public void test_should_index_action_works_fine() */
    public function test_should_index_action_works_fine()
    {
        $controller = new Controller();

        ob_start();
        $controller->execute('', array());
        $output = ob_get_contents();
        ob_clean();

        $this->assertContains('<!--STATUS OK-->', $output);

        $controller->execute('INdEX', array());
        $this->assertEquals(ob_get_contents(), $output);
        ob_end_clean();
    }
    /* }}} */

}

