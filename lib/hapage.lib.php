<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 模板类                    								   		  	    |
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Object')) {
    require_once(sprintf('%s/object.lib.php', dirname(__FILE__)));
}

class HA_Hapage extends HA_Object
{

    /* {{{ 静态变量 */

    /**
     * @模板源文件存放目录
     */
    private static $_tplPath  = '';

    /**
     * @模板目标文件存放目录
     */
    private static $_objPath  = '';

    /**
     * @初始化标记
     */
    private static $_bolInit  = false;

    /* }}} */

    /* {{{ 成员变量 */

    /**
     * @模板源文件
     */
    private $_strSrcFile;

    /**
     * @模板目标文件
     */
    private $_strObjFile;

    /**
     * @编译深度
     */
    private $_intComDepth;

    /* }}} */

    /* {{{ public Boolean __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return Boolean true
     */
    public function __construct($intDep = 5)
    {
        $this->_init();
        $this->_intComDepth = max(1, intval($intDep));

        if (self::$_bolInit !== true) {
            self::_init_path();
        }

        return true;
    }
    /* }}} */

    /* {{{ public Boolean setSrcFile() */
    /**
     * 设置模版源文件
     *
     * @access public
     * @param String $strSrc
     * @return Boolean true or false
     */
    public function setSrcFile($strSrc)
    {
        $strSrc = self::realpath($strSrc);
        if (!is_file($strSrc)) {
            self::_error(101, sprintf('源文件 "%s" 不存在', $strSrc));
            return false;
        }
        $this->_strSrcFile = $strSrc;

        return true;
    }
    /* }}} */

    /* {{{ public Boolean setObjFile() */
    /**
     * 设置编译目标文件
     *
     * @access public
     * @param String $strObj
     * @return Boolean true or false
     */
    public function setObjFile($strObj)
    {
        if (!is_scalar($strObj)) {
            self::_error(102, '目标文件参数错误');
            return false;
        }
        $this->_strObjFile = self::realpath($strObj);

        return true;
    }
    /* }}} */

    /* {{{ public Boolean compile() */
    /**
     * 编译模版文件
     *
     * @access public
     * @param  String $strTpl (default '')
     * @return Boolean true or false
     */
    public function compile($strTpl = '')
    {
        if (false === ($arrTmp = file($this->_strSrcFile))) {
            self::_error(103, sprintf('源文件 "%s" 读取失败', $this->_strSrcFile));
            return false;
        }

        $strVal = implode("\n", array_filter(array_map('trim', $arrTmp)));
        $strExp = '((\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\"\'\$\x7f-\xff]+\])*)';


        $strVal = preg_replace("/([\n\r]+)\t+/s", '\\1', $strVal);
        $strVal = preg_replace('/\<\!\-\-\{(.+?)\}\-\-\>/s', '{\\1}', $strVal);
        $strVal = preg_replace("/$strExp/es", "self::addquote('<?=\\1?>')", $strVal);

        $strVal = preg_replace(
            '/\s*\{element\s+(.+?)\}\s*/is',
            "\n<?php include(template('\\1', '_element', '$strTpl'));?>\n",
            $strVal
        );
        $strVal = preg_replace('/\s*\{lang\s+(.+?)\s+(.+?)\}\s*/is', "\n<?php __('\\1', '\\2'); ?>\n", $strVal);
        $strVal = preg_replace('/\s*\{lang\s+(.+?)\}\s*/is', "\n<?php __('\\1'); ?>\n", $strVal);
        $strVal = preg_replace('/\s*\{elseif\s+(.+?)\}\s*/ies', "self::stripvtags('\n<?php } elseif(\\1) { ?>\n','')", $strVal);
        $strVal = preg_replace('/\s*\{else\}\s*/is', "\n<? } else { ?>\n", $strVal);

        for($i = 0; $i < $this->_intComDepth; $i++) {
            $strVal = preg_replace(
                "/\s*\{loop\s+(\S+)\s+(\S+)\}\s*(.+?)\s*\{\/loop\}\s*/ies",
                "self::stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 AS \\2) { ?>','\n\\3\n<?php } } ?>\n')",
                $strVal
            );
            $strVal = preg_replace(
                "/\s*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}\s*(.+?)\s*\{\/loop\}\s*/ies",
                "self::stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 AS \\2 => \\3) { ?>','\n\\4\n<?php } } ?>\n')",
                $strVal
            );
            $strVal = preg_replace(
                "/\s*\{if\s+(.+?)\}\s*(.+?)\s*\{\/if\}\s*/ies",
                "self::stripvtags('\n<?php if(\\1) { ?>','\n\\2\n<?php } ?>\n')",
                $strVal
            );
        }

        $strVal = preg_replace('/ \?\>[\n\r]*\<\? /s', ' ', $strVal);
        $strVal = preg_replace('/\<\?\=(\$.+?)\?\>/s', self::addquote('<?php echo \\1;?>'), $strVal);
        $strVal = "<?php if (!defined('HA_APP_ROOT')) { exit('Access Denied!'); } ?>\n" . trim($strVal);

        $strDir = dirname($this->_strObjFile);
        if (!is_dir($strDir) && !mkdir($strDir, 0777, true)) {
            self::_error(104, sprintf('目标路径 "%s" 创建失败', $strDir));
            return false;
        }

        if (file_put_contents($this->_strObjFile, $strVal, LOCK_EX) !== strlen($strVal)) {
            self::_error(105, sprintf('目标文件 "%s" 写入失败', $this->_strObjFile));
            return false;
        }

        return true;
    }
    /* }}} */

    /* {{{ public static Boolean changed() */
    /**
     * 检查源文件是否有更新
     *
     * @access public static
     * @param  String $strHtm (refferrence)
     * @param  String $strObj (refferrence)
     * @return Boolean true or false
     */
    public static function changed(&$strHtm, &$strObj)
    {
        if (self::$_bolInit !== true) {
            self::_init_path();
        }

        $strHtm	= trim($strHtm);
        if (strncmp($strHtm, '/', 1) != 0) {
            $strHtm	= sprintf('%s/%s', self::$_tplPath, $strHtm);
        }
        $strHtm	= self::realpath($strHtm);

        $strObj	= trim($strObj);
        if (strncmp($strObj, '/', 1) != 0) {
            $strObj	= sprintf('%s/%s', self::$_objPath, $strObj);
        }
        $strObj	= self::realpath($strObj);

        $intOld	= is_file($strObj) ? filemtime($strObj) : 0;
        $intNew	= max(is_file($strHtm) ? filemtime($strHtm) : 0, filemtime(__FILE__));

        return $intOld < $intNew ? true : false;
    }
    /* }}} */

    /* {{{ private static String addquote() */
    /**
     * 添加引号
     *
     * @access private
     * @param String $var
     * @return String
     */
    private static function addquote($var)
    {
        $var = preg_replace('/\[([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\]/s', "['\\1']", $var);
        return str_replace('\\\"', '\"', $var);
    }
    /* }}} */

    /* {{{ private static String stripvtags() */
    /**
     * 过滤PHP tag
     *
     * @access private static
     * @param String $expr
     * @param String $statement
     * @return String
     */
    private static function stripvtags($expr, $statement)
    {
        $expr = preg_replace('/\<\?\=(\$.+?)\?\>/s', '\\1', $expr);
        $expr = str_replace('\\\"', '\"', $expr);
        $statement = str_replace("\\\"", "\"", $statement);

        return $expr . $statement;
    }
    /* }}} */

    /* {{{ private static Boolean _init_path() */
    /**
     * 初始化模板路径
     *
     * @access private static
     * @return Boolean true or false
     */
    private static function _init_path()
    {
        self::$_tplPath = sprintf('%s/theme', HA_APP_ROOT);

        if (!defined('TEMPLATE_OBJ_PATH')) {
            self::$_objPath = sprintf('%s/var/theme', HA_APP_ROOT);
        } else {
            self::$_objPath = TEMPLATE_OBJ_PATH;
        }
        self::$_bolInit = true;

        return true;
    }
    /* }}} */

}

function template($strAct, $strDir, $strTpl = 'default', $bolErr = false)
{
    $strAct	= strtolower(trim($strAct));
    $strDir	= strtolower(trim($strDir));
    $strTpl	= strtolower(trim($strTpl));
    $strTpl	= strlen($strTpl) > 0 ? $strTpl : 'default';

    $strObj	= sprintf('%s/%s/%s.tpl.php', $strTpl, $strDir, $strAct);
    $strHtm	= sprintf('%s/%s/%s.tpl.htm', $strTpl, $strDir, $strAct);

    if (HA_hapage::changed($strHtm, $strObj) !== false) {
        $objTpl	= new HA_hapage();
        $objTpl->setSrcFile($strHtm);
        $objTpl->setObjFile($strObj);
        if ($objTpl->compile($strTpl) !== true) {
            if ($bolErr !== true) {
                return template('error', $strDir, $strTpl, true);
            } else {
                return false;
            }
        }
    }

    return $strObj;
}

