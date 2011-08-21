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

    private $bfsize = 0;

    private $buffer = '';

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
    public static function chunk($fname, $slice, $spath = self::SPLIT_PATH)
    {
        $ob	= new self($fname);
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

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct($fname, $bfsize = self::BUFFER_SIZE)
    {
        $this->fname    = trim($fname);
        $this->bfsize   = (int)$bfsize;
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
        $this->close();
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

        $spath  = sprintf('%s/%s', realpath($spath), basename($this->fname));
        $slice  = (array)$slice;

        $buffer = '';
        $offset = current($slice);
        $wfname = $spath . '_' . key($slice);
        if (!$this->truncate($wfname)) {
            $this->close();
            return false;
        }

        $chunks = array($wfname);
        while (1) {
            $buffer .= fread($this->handle, $this->bfsize);
            if (false !== ($pos = strpos($buffer, self::END_OF_LINE, $offset - 1))) {
                if (!file_put_contents($wfname, substr($buffer, 0, $pos + 1), FILE_APPEND)) {
                    $this->error    = sprintf('Append to file "%s" failed.', $wfname);
                    $this->close();
                    return false;
                }

                $buffer = substr($buffer, $pos);
                if (false !== ($pos = next($slice))) {
                    $offset = $pos;
                    $wfname = $spath . '_' . key($slice);
                    if (!$this->truncate($wfname)) {
                        $this->close();
                        return false;
                    }
                    $chunks[]   = $wfname;
                }
            }

            if (feof($this->handle)) {
                // xxx: flush
                break;
            }
        }
        $this->close();

        return $chunks;
    }
    /* }}} */

    /* {{{ private Boolean truncate() */
    /**
     * 清理已有文件
     *
     * @access private
     * @return Boolean true or false
     */
    private function truncate($fname)
    {
        $rt = true;
        if (is_file($fname)) {
            if (!unlink($fname)) {
                $this->error    = sprintf('File "%s" already exists, and unlink failed.', $fname);
                $rt = false;
            }
        }

        return $rt;
    }
    /* }}} */

    /* {{{ private void close() */
    /**
     * 关闭文件句柄
     *
     * @access private
     * @return void
     */
    private function close()
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle   = null;
        }
    }
    /* }}} */

}
