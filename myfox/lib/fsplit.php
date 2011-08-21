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

    const SPLIT_PATH    = '/tmp/myfox/split';

    const BUFFER_SIZE   = 1048576;      /**<    1M */

    const END_OF_LINE   = "\n";

    /* }}} */

    /* {{{ 成员变量 */

    private $fname  = null;

    private $offset = 0;

    private $sline  = 0;                /**<    平均每行大小 */

    private $error  = null;

    private $handle = null;

    /* }}} */

    /* {{{ public static Mixture chunk() */
    /**
     * 按行切分文件
     *
     * @access public static
     * @return Mixture
     */
    public static function chunk($fname, $slice, $spath = self::SPLIT_PATH, $sline = 0)
    {
        $ob	= new self($fname, $sline);
        return $ob->split($slice, $spath);
    }
    /* }}} */

    /* {{{ public String lastError() */
    /**
     * 返回上次错误
     * @access public
     * @return String
     */
    public function lastError()
    {
        return $this->error;
    }
    /* }}} */

    /* {{{ public void __destruct() */
    /**
     * 析构函数
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle   = null;
        }
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
    private function split($slice, $spath = '')
    {
        $fn = realpath($this->fname);
        if (empty($fn)) {
            $this->error    = sprintf('No such file named as "%s".', $this->fname);
            return false;
        }

        if (!is_dir($spath) && !mkdir($spath, 0755, true)) {
            $this->error    = sprintf('Directory "%s" created failed.', $spath);
            return false;
        }

        $this->fname    = $fn;
        if (false === ($this->handle = fopen($this->fname, 'rb'))) {
            $this->error    = sprintf('File "%s" open failed.', $this->fname);
            return false;
        }

        if ($this->sline < 1 && !$this->test()) {
            return false;
        }

        $chunks = array();
        $spath  = sprintf('%s/%s', realpath($spath), basename($this->fname));

        $this->offset   = 0;

        foreach ((array)$slice AS $idx => $line) {
            $sname  = $spath . '_' . $idx;
            if (is_file($sname) && !unlink($sname)) {
                $this->error    = sprintf('File "%s" already exists, and unlink failed.', $sname);
                return false;
            }

            $chunks[]   = $sname;
            fseek($this->handle, $this->offset, SEEK_SET);
            $offset = (int)ceil(($line + 10) * $this->sline);
            $goon   = false;

            while (!feof($this->handle) && ($goon || $offset > 0)) {
                $buffer = fread($this->handle, self::BUFFER_SIZE);
                if (($offset -= self::BUFFER_SIZE) <= 0) {
                    if (false === ($ps = strrpos($buffer, self::END_OF_LINE))) {
                        $goon   = true;
                    } else {
                        $goon   = false;
                        $buffer = substr($buffer, 0, $ps + 1);
                    }
                }

                $this->offset   += strlen($buffer);
                if (false === file_put_contents($sname, $buffer, FILE_APPEND, null)) {
                    $this->error    = sprintf('File "%s" append failed.', $sname);
                    return false;
                }
            }
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
        fseek($this->handle, 0, SEEK_SET);
        if (false === ($buffer = fread($this->handle, self::BUFFER_SIZE))) {
            $this->error    = sprintf('File "%s" read failed.', $this->fname);
            return false;
        }

        $header = explode(self::END_OF_LINE, $buffer);
        if (empty($header) || !isset($header[1])) {
            $this->error    = sprintf('Line is longer than %d, or bad formmat.', strlen($buffer));
            return false;
        }

        array_pop($header);
        $this->sline    = strlen(implode(self::END_OF_LINE, $header)) / count($header);

        return true;
    }
    /* }}} */

}
