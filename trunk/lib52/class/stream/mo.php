<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | stream\mo.php	Mo文件解析											|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Aleafs.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@sohu.com>								|
// +--------------------------------------------------------------------+
//
// $Id: mo.php 94 2010-06-02 13:43:29Z zhangxc83 $

class Aleafs_Lib_Stream_Mo
{

    /* {{{ 成员变量 */

    private $mofile = null;

    private $mosize = 0;

    private $iotime = 0;

    private $findtm = 0;

    private $isload = false;

    private $header     = array();
    /**
     * @原始语言存放字典
     */
    private $original   = array();

    /**
     * @翻译结果存放字典
     */
    private $translate  = array();

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @param  String $file
     * @return void
     */
    public function __construct($file)
    {
        $this->mofile   = realpath($file);
        if (empty($this->mofile)) {
            throw new Aleafs_Lib_Exception(sprintf('No such mo file as "%s"', $file));
        }

        $this->mosize   = filesize($this->mofile);
        $this->iotime   = 0;
    }
    /* }}} */

    /* {{{ public Mixture gettext() */
    /**
     * 获取字符串翻译
     *
     * @access public
     * @param  String $string
     * @return String or Boolean false
     */
    public function gettext($string)
    {
        if (empty($this->isload) && !$this->_load()) {
            return false;
        }

        $intIdx = $this->find($string);
        if ($intIdx < 0) {
            return false;
        }

        return $this->_get_translate($intIdx);
    }
    /* }}} */

    /* {{{ public Mixture debugInfo() */
    /**
     * 获取调试信息
     *
     * @access public
     * @return Mixture
     */
    public function debugInfo()
    {
        return array(
            'mofile'    => $this->mofile,
            'iotime'    => $this->iotime,
            'search'    => $this->findtm,
        );
    }
    /* }}} */

    /* {{{ private Boolean _load() */
    /**
     * 加载mo文件
     *
     * @access private
     * @return Boolean true or false
     */
    private function _load()
    {
        $this->isload   = true;
        if (!$this->parseHead($this->_read(0, 20))) {
            return false;
        }

        $unpack = sprintf('%s%d', $this->header['pack'], 2 * $this->header['count']);
        $intAll = 8 * $this->header['count'];

        $this->original = unpack($unpack, $this->_read($this->header['off_o'], $intAll));
        $this->translate= unpack($unpack, $this->_read($this->header['off_t'], $intAll));

        return true;
    }
    /* }}} */

    /* {{{ private Mixture parseHead() */
    /**
     * 读取MO文件头信息
     *
     * @access private
     * @param  String $data
     * @return Mixture
     */
    private function parseHead($data)
    {
        $intVal = unpack('V', substr($data, 0, 4));
        $intVal = array_shift($intVal);

        $MAGIC1 = (int) - 1794895138;
        // $MAGIC2 = (int)0xde120495; //bug
        $MAGIC2 = (int) - 569244523;
        // 64-bit fix
        $MAGIC3 = (int) 2500072158;

        if ($intVal == $MAGIC1 || $intVal == $MAGIC3) {
            $strVal = 'V';        /**<  little endian      */
        } elseif ($intVal == ($MAGIC2 & 0xFFFFFFFF)) {
            $strVal = 'N';        /**<  big endian      */
        } else {
            return false;
        }

        $arrVal = unpack(sprintf('%s4', $strVal), substr($data, 4));
        $this->header = array(
            'pack'  => $strVal,
            'rev'   => $arrVal[1],
            'count' => $arrVal[2],
            'off_o' => $arrVal[3],
            'off_t' => $arrVal[4],
        );

        return true;
    }
    /* }}} */

    /* {{{ private String  _read() */
    /**
     * 读取文件固定长度
     *
     * @access private
     * @param  Integer $off
     * @param  Integer $len
     * @return String
     */
    private function _read($off, $len)
    {
        $len = (int)$len;
        if ($len < 1) {
            return '';
        }

        $this->iotime++;

        $off = (int)$off;
        $off = $off < 0 ? $this->mosize + $off : $off;

        return (string)file_get_contents(
            $this->mofile, false, null, $off,
            min($len, $this->mosize - $off)
        );
    }
    /* }}} */

    /* {{{ private Integer find() */
    /**
     * 二分法查找原始字符串
     *
     * @access private
     * @param  String $string
     * @return Integer
     */
    private function find($string, $beg = -1, $end = -1)
    {
        if ($beg < 0 || $end < 0) {
            $beg = 0;
            $end = $this->header['count'];
        }

        $this->findtm++;
        if (abs($end - $beg) <= 1) {
            if (0 == strcmp($string, $this->_get_original($beg))) {
                return $beg;
            }

            return -1;
        }

        if ($beg > $end) {
            return $this->find($string, $end, $beg);
        }

        $index  = (int)(($beg + $end) / 2);
        $cmp    = strcmp($string, $this->_get_original($index));
        if ($cmp > 0) {
            return $this->find($string, $index, $end);
        }

        if ($cmp < 0) {
            return $this->find($string, $beg, $index);
        }

        return $index;
    }
    /* }}} */

    /* {{{ private String _get_original() */
    /**
     * 获取原始字符串
     *
     * @access private
     * @param  Integer $index
     * @return String
     */
    private function _get_original($index)
    {
        $index *= 2;
        return $this->_read(
            $this->original[$index + 2],
            $this->original[$index + 1]
        );
    }
    /* }}} */

    /* {{{ private String _get_translate() */
    /**
     * 获取翻译字符串
     *
     * @access private
     * @param  Integer $index
     * @return String
     */
    private function _get_translate($index)
    {
        $index *= 2;
        return $this->_read(
            $this->translate[$index + 2],
            $this->translate[$index + 1]
        );
    }
    /* }}} */

}

