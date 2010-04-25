<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Render\Html.php	       											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: html.php 2010-04-23  aleafs Exp $

namespace Aleafs\Lib\Render;

class Html
{

    /* {{{ 静态常量 */

    /**
     * @用于判断模板文件是否上传完成
     */
    const TPL_COMPLETE_CHAR	= '<!--COMPLETE-->';

    /* }}} */

    /* {{{ 静态变量 */

    private static $ini = array(
        'tpl_path'  => null,
        'obj_path'  => null,
        'theme'     => 'default',
        'expire'    => 0, 
    );

    /* }}} */

    /* {{{ 成员变量 */
    /**
     * @绑定的数据 
     */
    private $data	= array();

    /* }}} */

    /* {{{ public static void init() */
    /**
     * 初始化Html渲染器
     *
     * @access public static
     * @param  Mixture $ini
     * @return void
     */
    public static function init($ini)
    {
        if (!is_array($ini) || empty($ini)) {
            return;
        }

        self::$ini = array_merge(self::$ini, $ini);
    }
    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->cleanAllParams();
    }
    /* }}} */

    /* {{{ public Object assign() */
    /**
     * 绑定参数
     *
     * @access public
     * @param  String $key
     * @param  Mixture $val
     * @return Object $this
     */
    public function assign($key, $val)
    {
        $this->data[trim($key)] = $val;
        return $this;
    }
    /* }}} */

    /* {{{ public Object cleanAllParams() */
    /**
     * 清理所有绑定的参数
     *
     * @access public
     * @return Object $this
     */
    public function cleanAllParams()
    {
        $this->data	= array();
        return $this;
    }
    /* }}} */

    /* {{{ public void render() */
    /**
     * 渲染并输出
     *
     * @access public
     * @param  String $tplFile
     * @param  String $tplDir
     * @return void
     */
    public function render($tplName, $tplDir)
    {
        $tplSrc = sprintf(
            '%s/%s/%s/%s.htm',
            self::$ini['tpl_path'],
            self::$ini['theme'],
            $tplDir, $tplName
        );

        $tplObj = sprintf(
            '%s/%s/%s/%s.php',
            self::$ini['obj_path'],
            self::$ini['theme'],
            $tplDir, $tplName
        );

        if (!is_file($tplObj)) {

        }

        extract($this->data);
        include($tplObj);
    }
    /* }}} */

    /* {{{ private static Boolean compile() */
    /**
     * 编译模板文件
     *
     * @access private static
     * @param  String $tplSrc
     * @param  String $tplObj
     * @return Boolean true or false
     */
    private static function compile($tplSrc, $tplObj)
    {
    }
    /* }}} */

}

