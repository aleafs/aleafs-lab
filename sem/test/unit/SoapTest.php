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

    private $webroot;

    /* {{{ protected void setUp() */
    protected function setUp()
    {
        parent::setUp();
        self::registerDefault(__DIR__ . '/ini/global.ini');

        $config = Aleafs_Lib_Configer::instance('default');
        $global = rtrim($config->get('url.server', ''), '/');
        $prefix = $config->get('url.prefix', '');
        if (!empty($prefix)) {
            $global = $global . '/' . trim($prefix, '/');
        }

        $this->webroot  = $global;
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

        $client = new SoapClient(
            sprintf('%s/soap/math/wsdl', $this->webroot),
            array(
                'encoding'  => 'utf-8',
                'cache_wsdl'    => WSDL_CACHE_NONE,
            )
        );

        $this->assertEquals(array(
            'AddResponse add(AddRequest $parameters)',
        ), $client->__getFunctions());
        $header = new SoapHeader(
            sprintf('%s/soap/math', $this->webroot),
            'AuthHeader',
            json_decode(json_encode(array(
                'username'  => 'unittest',
                'password'  => '123456',
                'token'     => '123456',
            )))
        );

        $result = $client->__soapCall(
            'add', array(json_decode(json_encode(array('a' => 50, 'b' => 20)))),
            null, $header, $header
        );

        $this->assertEquals(array('sum' => 70), (array)$result);
        $this->assertEquals(array(
            'status'    => 1,
            'error'     => 'fuck',
        ), (array)$header['ResHeader']);
    }
    /* }}} */

    /* {{{ public void test_should_access_heartbeat_works_fine() */
    public function test_should_access_heartbeat_works_fine()
    {
        $client = new SoapClient(
            sprintf('%s/soap/access/wsdl', $this->webroot),
            array(
                'encoding'  => 'utf-8',
                'cache_wsdl'    => WSDL_CACHE_NONE,
            )
        );

        $this->assertEquals(array(
            'Permissions permission(ClientAgent $ClientAgent)',
            'ResCallBack heartbeat(ClientAgent_HB $ClientAgent_HB)',
            'ClientAgent_VS version(ClientAgent_VS $ClientAgent_VS)',
        ), $client->__getFunctions());

        $header = new SoapHeader(
            sprintf('%s/soap/access', $this->webroot),
            'AuthHeader',
            json_decode(json_encode(array(
                'appname'   => 'baidu',
                'username'  => 'functest',
                'machine'   => '123456',
                'nodename'  => php_uname('n'),
            )))
        );

        $result = $client->__soapCall(
            'heartbeat', array(json_decode(json_encode(array('software' => 'PHP', 'version' => '5.3.2')))),
            null, $header, $header
        );

        $this->assertEquals(array(
            'status'        => 0,
            'description'   => '',
        ), (array)$header['ResHeader']);

        $this->assertEquals(array(
            'feedback' => 'access/heartbeat',
            'function' => 'PHP',
            'args' => '5.3.2',
        ), json_decode(json_encode($result), true));
    }
    /* }}} */

    /* {{{ public void test_should_access_permission_works_fine() */
    public function test_should_access_permission_works_fine()
    {
        $client = new SoapClient(
            sprintf('%s/soap/access/wsdl', $this->webroot),
            array(
                'encoding'  => 'utf-8',
                'cache_wsdl'    => WSDL_CACHE_NONE,
            )
        );

        $header = new SoapHeader(
            sprintf('%s/soap/access', $this->webroot),
            'AuthHeader',
            json_decode(json_encode(array(
                'appname'   => 'baidu',
                'username'  => 'functest',
                'machine'   => '123456',
                'nodename'  => php_uname('n'),
            )))
        );

        $result = $client->__soapCall(
            'permission', array('a'), null, $header, $header
        );

        $this->assertEquals(array(
            'status'        => 0,
            'description'   => '',
        ), (array)$header['ResHeader']);

        $result = json_decode(json_encode($result->perms), true);

    }
    /* }}} */

}
