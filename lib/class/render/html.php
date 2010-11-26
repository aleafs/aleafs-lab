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

use \Aleafs\Lib\Language;
use \Aleafs\Lib\Exception;

class Html
{

    /* {{{ 静态常量 */

    /**
     * @用于判断模板文件是否上传完成
     */
    const TPL_COMPLETE_CHAR	= '<!--COMPLETE-->';

    const FILE_MAX_LOCK_TM  = 2;          /**<  最长加锁时间      */

    const COMPILE_LOOP_DEEP = 5;

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

    /**
     * @文件编译次数
     */
    public $complie = array();

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

    /* {{{ public Object __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return Object $this
     */
    public function __construct()
    {
        return $this->clean();
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

    /* {{{ public Object clean() */
    /**
     * 清理所有绑定的参数
     *
     * @access public
     * @return Object $this
     */
    public function clean()
    {
        $this->data	= array();
        return $this;
    }
    /* }}} */

    /* {{{ public Mixture render() */
    /**
     * 渲染并输出
     *
     * @access public
     * @param  String $tplFile
     * @param  String $tplDir
     * @param  Boolean $flush : true
     * @return Mixture
     */
    public function render($tplName, $tplDir, $flush = true)
    {
        extract($this->data);

        ob_start();
        include($this->template($tplName, $tplDir));
        $data = trim(ob_get_contents());
        ob_end_clean();

        if (false !== $flush) {
            echo $data;
            flush();
        }

        return $data;
    }
    /* }}} */

    /* {{{ private String template() */
    /**
     * 获取编译后的模板文件名
     *
     * @access private
     * @param  String $tplFile
     * @param  String $tplDir
     * @return String
     */
    private function template($tplName, $tplDir)
    {
        $tplObj = sprintf(
            '%s/%s/%s/%s.php',
            self::$ini['obj_path'],
            self::$ini['theme'],
            $tplDir, $tplName
        );

        if (empty(self::$ini['expire']) && is_file($tplObj)) {
            return $tplObj;
        }

        $themes = array_unique(array(
            self::$ini['theme'],
            'default',
        ));

        $error  = true;
        foreach ($themes AS $theme) {
            $tplSrc = sprintf(
                '%s/%s/%s/%s.htm',
                self::$ini['tpl_path'],
                $theme,
                $tplDir, $tplName
            );
            if (is_file($tplSrc)) {
                $error = false;
                break;
            }
        }

        if ($error) {
            throw new Exception(sprintf(
                'No such template source file, path:[%s], theme:[%s], dir:[%s], name:[%s]',
                self::$ini['tpl_path'], self::$ini['theme'],
                $tplDir, $tplName
            ));
        }

        $objTime = is_file($tplObj) ? filemtime($tplObj) : 0;
        if ($objTime < filemtime($tplSrc) || $objTime + self::$ini['expire'] < time()) {
            $data = array_filter(array_map('trim', (array)file($tplSrc)));
            if (0 != strcasecmp(array_pop($data), self::TPL_COMPLETE_CHAR)) {
                throw new Exception(sprintf(
                    'Template source file "%s" is uncompleted.',
                    $tplSrc
                ));
            }

            $this->compile(implode("\n", $data), $tplObj);

            $index  = sprintf('%s/%s', $tplDir, $tplName);
            $this->compile[$index] = isset($this->compile[$index]) ? $this->compile[$index] + 1 : 1;
        }

        return $tplObj;
    }
    /* }}} */

    /* {{{ private static String lang() */
    /**
     * 语言包进行翻译
     *
     * @access private static
     * @param  String $string
     * @param  String $domain : default null
     * @return String
     */
    private static function lang($string, $domain = null)
    {
        return Language::translate($string, $domain);
    }
    /* }}} */

    /* {{{ private static Boolean compile() */
    /**
     * 编译模板文件
     *
     * @access private static
     * @param  String $content : 模板内容
     * @param  String $tplObj  : 模板文件
     * @return Boolean true or false
     */
    private static function compile($content, $tplObj)
    {
        $strExp = '((\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\"\'\$\x7f-\xff]+\])*)';

        $content = preg_replace("/([\n\r]+)\t+/s", '\\1', $content);
        $content = preg_replace('/\<\!\-\-\{(.+?)\}\-\-\>/s', '{\\1}', $content);
        $content = preg_replace("/$strExp/es", "self::addquote('<?=\\1?>')", $content);

        $content = preg_replace(
            '/\s*\{element\s+(.+?)\}\s*/is',
            "\n<?php include(\$this->template('\\1', '_element'));?>\n",
            $content
        );

        $content = preg_replace(
            '/\s*\{lang\s+(.+?)\s+(.+?)\}\s*/is',
            "\n<?php echo self::lang('\\1', '\\2'); ?>\n",
            $content
        );
        $content = preg_replace(
            '/\s*\{lang\s+(.+?)\}\s*/is',
            "\n<?php echo self::lang('\\1'); ?>\n",
            $content
        );
        $content = preg_replace(
            '/\s*\{elseif\s+(.+?)\}\s*/ies',
            "self::stripvtags('\n<?php } elseif (\\1) { ?>\n','')",
            $content
        );
        $content = preg_replace('/\s*\{else\}\s*/is', "\n<? } else { ?>\n", $content);

        for($i = 0; $i < self::COMPILE_LOOP_DEEP; $i++) {
            $content = preg_replace(
                "/\s*\{loop\s+(\S+)\s+(\S+)\}\s*(.+?)\s*\{\/loop\}\s*/ies",
                "self::stripvtags('\n<?php foreach ((array)\\1 AS \\2) { ?>','\n\\3\n<?php } ?>\n')",
                $content
            );
            $content = preg_replace(
                "/\s*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}\s*(.+?)\s*\{\/loop\}\s*/ies",
                "self::stripvtags('\n<?php foreach ((array)\\1 AS \\2 => \\3) { ?>','\n\\4\n<?php } ?>\n')",
                $content
            );
            $content = preg_replace(
                "/\s*\{if\s+(.+?)\}\s*(.+?)\s*\{\/if\}\s*/ies",
                "self::stripvtags('\n<?php if(\\1) { ?>','\n\\2\n<?php } ?>\n')",
                $content
            );
        }

        $content = preg_replace('/ \?\>[\n\r]*\<\? /s', ' ', $content);
        $content = preg_replace('/\<\?\=(\$.+?)\?\>/s', self::addquote('<?php echo \\1;?>'), $content);

        $lock = $tplObj;
        if (!self::lock($lock, self::FILE_MAX_LOCK_TM)) {
            throw new Exception(sprintf(
                'Template file "%s" lock error, expire : %ds',
                $tplObj, self::FILE_MAX_LOCK_TM
            ));
        }

        $len1 = strlen($content);
        $len2 = file_put_contents($lock, $content, LOCK_EX);
        if ($len1 != $len2 || !rename($lock, $tplObj)) {
            self::unlock($lock);
            throw new Exception(sprintf(
                'Template file "%s" flush error, size : %d, write : %d',
                $tplObj, $len1, $len2
            ));
        }
        self::unlock($lock);
    }
    /* }}} */

    /* {{{ private static String  realpath() */
    /**
     * 计算文件的完整路径名
     *
     * @access private static
     * @param  String $file
     * @return Boolean true or false
     */
    private static function realpath($file)
    {
        if (!is_scalar($file) || trim($file) == '') {
            return false;
        }

        if (false !== ($temp = realpath($file))) {
            return $temp;
        }

        $temp = explode(DIRECTORY_SEPARATOR, $file);
        $keys = array_keys($temp, '..');
        foreach ($keys AS $pos => $key) {
            array_splice($temp, $key - ($pos * 2 + 1), 2);
        }

        return strtr(
            implode(DIRECTORY_SEPARATOR, $temp),
            array('./' => '', '.\\' => '', )
        );
    }
    /* }}} */

    /* {{{ private static Boolean lock() */
    /**
     * 文件锁定
     *
     * @access private static
     * @param  String  $file : 文件名 (refferrence)
     * @param  Integer $time : 锁失效时间 (s)
     * @return Boolean true or false
     */
    private static function lock(&$file, $time)
    {
        if (false === ($temp = self::realpath($file))) {
            return false;
        }

        $file = $temp . '.lock';
        $path = dirname($file);
        if (!is_dir($path) && !mkdir($path, 0744, true)) {
            return false;
        }

        $time = max(0, (int)$time);
        $time = empty($time) ? 0 : time() - (int)$time;
        if (!is_file($file) || ($time > 0 && (filemtime($file) + $time) <= time())) {
            return touch($file) ? true : false;
        }

        $sum  = 0;
        $step = 2000;         /**<  2ms      */
        $need = 1000000 * ($time + filemtime($file) - time());
        $max  = $time * 1000000;
        for ($i = 0;;$i++) {
            $add = $i * $step;
            usleep($add);
            if (!is_file($file)) {
                return true;
            }

            $sum += $add;
            if ($sum >= $need) {
                self::unlock($file);
                return true;
            }

            if ($sum >= $max) {
                return false;
            }
        }

        return false;
    }
    /* }}} */

    /* {{{ private static Boolean unlock() */
    /**
     * 文件解锁
     *
     * @access private static
     * @param  String  $lock : 锁文件名
     * @return Boolean true or false
     */
    private static function unlock($lock)
    {
        if (!is_file($lock)) {
            return true;
        }

        if (unlink($lock)) {
            return true;
        }

        return touch($lock, 0);
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

}

