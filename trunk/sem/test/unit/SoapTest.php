<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | SoapTest.php									    				|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: SoapTest.php 2010-06-01 aleafs Exp $

require_once(__DIR__ . '/../../class/sem/TestShell.php');

class Aleafs_Sem_SoapTest extends Aleafs_Sem_TestShell
{

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();
        self::registerDefault(__DIR__ . '/ini/global.ini');
    }
    /* }}} */

    /* {{{ protected void tearDown() */
    protected function tearDown()
    {
        parent::tearDown();
    }
    /* }}} */

    /* {{{ public void test_should_math_add_works_fine() */
    public function test_should_math_add_works_fine()
    {
        $config = Aleafs_Lib_Configer::instance('default');
        $global = rtrim($config->get('url.server', ''), '/');
        $prefix = $config->get('url.prefix', '');
        if (!empty($prefix)) {
            $global = $global . '/' . trim($prefix, '/');
        }

        $client = new SoapClient(
            sprintf('%s/soap/math/wsdl', $global),
            array(
                'encoding'  => 'utf-8',
            )
        );

        $header = new SoapHeader(
            sprintf('%s/soap/math', $global),
            'AuthHeader',
            json_decode(json_encode(array(
                'username'  => 'unittest',
                'password'  => '123456',
                'token'     => '123456',
            )))
        );

        $result = $client->__soapCall(
            'add', array(json_decode(json_encode(array('a' => 50, 'b' => 20)))),
            array(), $header, $header
        );

        $this->assertEquals(array('sum' => 70), (array)$result);
        $this->assertEquals(array(
            'status'    => 1,
            'error'     => 'fuck',
        ), (array)$header['ResHeader']);
    }
    /* }}} */

}
