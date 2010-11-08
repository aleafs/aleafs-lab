<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | URL解析类			 					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2003 - 2010 Aleafs.com. All Rights Reserved				|
// +------------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+

class Aleafs_Lib_Parser_Url
{

    /* {{{ 成员变量 */

    private $ordinaryUrl;
    private $module;
    private $action;
    private $param;

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param String $restUrl
     * @return void
     */
    public function __construct($restUrl)
    {
        $this->ordinaryUrl  = trim(ltrim($restUrl, '/'));
        $this->parse();
    }
    /* }}} */

    /* {{{ public Mixture __get() */
    /**
     * 读取变量的魔术方法
     *
     * @access public
     * @return Mixture
     */
    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }
    /* }}} */

    /* {{{ public Mixture param() */
    /**
     * 获取URL中的参数值
     *
     * @access public
     * @return Mixture
     */
    public function param($key, $default = null)
    {
        return isset($this->param[$key]) ? $this->param[$key] : $default;
    }
    /* }}} */

    /* {{{ public static string build() */
    /**
     * 构造URL
     *
     * @access public static
     * @param String $module
     * @param String $action
     * @param Mixture $param : default null
     * @return String
     */
    public static function build($module, $action, $param = null)
    {
        $parts  = array(
            self::escape($module),
            self::escape($action),
        );
        foreach ((array)$param AS $key => $val) {
            if (!is_scalar($val)) {
                continue;
            }
            $parts[] = sprintf('%s/%s', self::escape($key), urlencode($val));
        }

        return implode('/', $parts);
    }
    /* }}} */

    /* {{{ private void parse() */
    /**
     * URL解析程序
     *
     * @access private
     * @return void
     */
    private function parse()
    {
        $urls = explode('?', $this->ordinaryUrl);
        $urls = array_values(array_filter(array_map('trim',
            explode('/', reset($urls))
        ), 'strlen'));
        $this->module	= isset($urls[0]) ? self::escape($urls[0]) : '';
        $this->action	= isset($urls[1]) ? self::escape($urls[1]) : '';
        $this->param	= array();

        for ($i = 2, $max = count($urls); $i < $max; $i++) {
            $name	= self::escape($urls[$i]);
            if (!isset($urls[++$i])) {
                $this->param[$name] = true;
            } else {
                $this->param[$name] = urldecode($urls[$i]);
            }
        }
    }
    /* }}} */

    /* {{{ private static String escape() */
    /**
     * 过滤URL中的非安全字符
     *
     * @access private static
     * @param String $str
     * @return String
     */
    private static function escape($str)
    {
        return trim(preg_replace('/[^\w]/is', '', $str));
    }
    /* }}} */

}
