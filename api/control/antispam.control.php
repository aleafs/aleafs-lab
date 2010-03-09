<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 反垃圾留言控制器													|
// +--------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@gmail.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Control_Abstract')) {
    exit('Access Denied!');
}

class App_Control_Antispam extends HA_Control_Abstract
{

    /* {{{ 静态变量 */

    /* }}} */

    /* {{{ protected Boolean _before_excute() */
    /**
     * 执行前触发器
     *
     * @access protected
     * @return Boolean true or false
     */
    protected function _before_excute()
    {
        $this->_loadModel('api', 'antispam');
    }
    /* }}} */

    /* {{{ protected Mixture _actionIndex() */
    /**
     * 默认action
     *
     * @access protected
     * @return Mixture
     */
    protected function _actionIndex()
    {
        $objRes = HA_Context::instance()->setid(true);
        $strRef = trim($objRes->referer());

        if (strlen($strRef) < 2) {
            self::_error(400, 'empty referer');
            return;
        }

return 0;
        $objMod = App_Model_Antispam::instance($strRef);
        if (empty($strRef)) {
            self::_error(401, sprintf('parse host from "%s" error', $objRes->referer()));
            return;
        }

        $objApi = $objMod->api();
        if ($objApi->status(App_Model_Api::API_STATUS_RUN)) {

        } elseif ($objApi->status(App_Model_Api::API_STATUS_WAIT)) {
            $objApi->set(array(
                'statid'  => App_Model_Api::API_STATUS_RUN,
                'efttime' => time(),
            ));
        } else {
            return;
        }

        $objMod->session();
    }
    /* }}} */

    /* {{{ protected Mixture _actionCheck() */
    /**
     * POST请求验证
     *
     * @access protected
     * @return Mixture
     */
    protected function _actionCheck()
    {
    }
    /* }}} */

    /* {{{ protected Mixture _actionPrison() */
    /**
     * 封禁漏杀的留言
     *
     * @access protected
     * @return Mixture
     */
    protected function _actionPrison()
    {
    }
    /* }}} */

    /* {{{ protected Mixture _actionFree() */
    /**
     * 释放误杀的留言
     *
     * @access protected
     * @return Mixture
     */
    protected function _actionFree()
    {
    }
    /* }}} */

}

