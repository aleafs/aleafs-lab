<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 文件获取类		    					    							|
// +------------------------------------------------------------------------+
// | Author: aleafs <pengchun@taobao.com>	    							|
// +------------------------------------------------------------------------+
//
// $Id: fileset.php 22 2010-04-15 16:28:45Z zhangxc83 $

namespace Myfox\Lib;

class Fsplit
{

    /* {{{ 静态常量 */

    const BUFFER_SIZE   = 1048576;      /**<    1M */

    const END_OF_LINE   = '\n';

    /* }}} */

    /* {{{ 成员变量 */

    private $fname  = null;

    private $fsize  = null;

    private $offset = 0;

    private $sline  = 0;                /**<    平均每行大小 */

    private $error  = null;

    /* }}} */

    /* {{{ public static Mixture chunk() */
    /**
     * 按行切分文件
     *
     * @access public static
     * @return Mixture
     */
    public static function chunk($fname, $slice, $sline = 0)
    {
        $ob	= new self($fname, $sline);
        return $ob->split($slice);
    }
    /* }}} */

    /* {{{ public String lastError() */
    /**
     * 返回上次错误
     *
     * @access public
     * @return String
     */
    public function lastError()
    {
        return $this->error;
    }
    /* }}} */

    /* {{{ private void __construct() */
    /**
     * 构造函数
     *
     * @access private
     * @return void
     */
    private function __construct($fname, $sline = 0)
    {
        $this->fname    = trim($fname);
        $this->sline    = 0 + $sline;
        $this->offset   = 0;
    }
    /* }}} */

    /* {{{ private void split() */
    /**
     * 进行文件切分
     *
     * @access private
     * @return void
     */
    private function split($slice)
    {
        $fn = realpath($this->fname);
        if (empty($fn)) {
            $this->error    = sprintf('No such file named as "%s".', $this->fname);
            return false;
        }

        $this->fname    = $fn;
        $this->fsize    = filesize($fn);
        if ($this->sline < 1 && empty($this->test())) {
            return false;
        }

        $chunks = array();
        foreach ((array)$slice AS $line) {
            $buffer = ceil($line * $this->sline);
        }

        return $chunks;
    }
    /* }}} */

    /* {{{ private Boolean test() */
    /**
     * 探测文件行大小
     *
     * @access private
     * @return Boolean true or false
     */
    private function test()
    {
        $buffer = min($this->fsize, self::BUFFER_SIZE);
        $header = explode(self::END_OF_LINE, 
            (string)@file_get_contents($this->fname, false, null, 0, $buffer)
        );

        if (empty($header) || !isset($header[1])) {
            $this->error    = sprintf(
                'File "%s" read failed, or has longer line than %d.',
                $this->fname, $buffer
            );
            return false;
        }

        array_pop($header);
        $this->sline    = strlen(implode(self::END_OF_LINE, $header)) / count($header);

        return true;
    }
    /* }}} */

}
