<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | 文本读取类															|
// +--------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: aleafs <zhangxc83@gmail.com>								|
// +--------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Object')) {
    require_once(sprintf('%s/object.lib.php', dirname(__FILE__)));
}

class HA_Txtread extends HA_Object
{

    /* {{{ 常量 */

    /**
     * @读取缓存
     */
    const READ_BUFFER   = 10485760;       /**< 10M       */

    /**
     * @读取返回码
     */
    const READ_STATUS_ERROR = 0;          /**<  出错了      */
    const READ_STATUS_MORE  = 1;          /**<  继续      */
    const READ_STATUS_EOF   = 2;          /**<  完成      */

    /* }}} */

    /* {{{ 成员变量 */

    /**
     * @文件名
     */
    private $_filename;

    /**
     * @大小
     */
    private $_filesize;

    /**
     * @文件游标
     */
    private $_fileoff;

    /**
     * @缓存游标
     */
    private $_readidx;

    /**
     * @读取状态
     */
    private $_status    = self::READ_STATUS_EOF;

    /**
     * @数据缓存
     */
    private $_caches;

    /**
     * @待返回的二维数组
     */
    private $_return    = array();

    /**
     * @列映射表
     */
    private $_arrMap;

    /**
     * @列数
     */
    private $_intCol;

    /**
     * @IO次数
     */
    private $_iotimes   = 0;

    /* }}} */

    /* {{{ public Boolean __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return Boolean true
     */
    public function __construct()
    {
        $this->_objRes  = new TxtRead_Parser_Default();
        return $this->_init();
    }
    /* }}} */

    /* {{{ public Object clear() */
    /**
     * 清理对象缓存
     *
     * @access public
     * @return Object $this
     */
    public function clear()
    {
        $this->_filename = null;
        $this->_caches   = null;
        $this->_filesize = 0;
        $this->_fileoff  = 0;
        $this->_readidx  = 0;
        $this->_return   = array();
        $this->_status   = self::READ_STATUS_EOF;

        return $this;
    }
    /* }}} */

    /* {{{ public Object filename() */
    /**
     * 设置文件名
     *
     * @access public
     * @param  String $filename
     * @return Object $this
     */
    public function filename($filename)
    {
        if (!is_scalar($filename)) {
            self::_error(401, 'Wrong type for filename');
        } else {
            $filename = self::realpath($filename);
            if (!is_file($filename)) {
                self::_error(402, sprintf('file "%s" not exists', $filename));
            } elseif ($filename != $this->_filename) {
                $this->_filename = $filename;
                $this->_filesize = filesize($this->_filename);
                $this->_fileoff  = 0;
                $this->_readidx  = 0;
                $this->_iotimes  = 0;
                $this->_caches   = '';
                $this->_status   = self::READ_STATUS_MORE;
            }
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Object parser() */
    /**
     * 设置解析对象
     *
     * @access public
     * @param  Object or String $parser
     * @return Object $this
     */
    public function parser($parser)
    {
        if ($parser instanceof TxtRead_Parser_Interface) {
            $this->_objRes = $parser;
        } elseif (is_string($parser)) {
            $strCls = sprintf('TxtRead_Parser_%s', ucfirst(strtolower(trim($parser))));
            if (class_exists($strCls) && ($strCls instanceof TxtRead_Parser_Interface)) {
                $this->_objRes  = new $strCls();
            } else {
                self::_error(700, sprintf('Undefined TxtRead Parser as "%s"', $parser));
            }
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Object setmap() */
    /**
     * 设置列的映射规则
     *
     * @access public
     * @param  Array $arrMap
     * @return Object $this
     */
    public function setmap($arrMap)
    {
        if (is_array($arrMap) && !empty($arrMap)) {
            $this->_arrMap  = array_values($arrMap);
            $this->_intCol  = count($this->_arrMap);
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Mixture fetch() */
    /**
     * 取出数据
     *
     * @access public
     * @return Mixture
     */
    public function fetch()
    {
        if (isset($this->_return[$this->_readidx])) {
            return $this->_return[$this->_readidx++];
        }

        if ($this->_status == self::READ_STATUS_EOF) {
            return false;
        }

        do {
            $this->_status  = $this->_read_file(-1);
            if ($this->_status == self::READ_STATUS_ERROR) {
                return false;
            }

            $bolEof = ($this->_status == self::READ_STATUS_EOF) ? true : false;
            $mixRet = $this->_objRes->explode($this->_caches, $bolEof);
            if (!is_array($mixRet)) {
                return false;
            }
        }
        while (empty($mixRet) && $bolEof !== true);

        $this->_readidx = 0;
        $this->_return  = array();

        $mixRet = $this->_row_filter($mixRet);
        if (!is_array($this->_arrMap) || empty($this->_arrMap)) {
            $this->_return  = $mixRet;
        } else {
            $intIdx = 0;
            $intCnt = count($this->_arrMap);
            foreach ($mixRet AS $arrRow) {
                $this->_return[$intIdx++] = array_combine($this->_arrMap, array_slice($arrRow, 0, $intCnt));
            }
        }

        return $this->_return[$this->_readidx++];
    }
    /* }}} */

    /* {{{ private Interger _read_file() */
    /**
     * 读取文件数据
     *
     * @access private
     * @param  Interger $intOff
     * @return Interger
     */
    private function _read_file($intOff)
    {
        $intOff = (int)$intOff;
        if ($intOff >= $this->_filesize) {
            return self::READ_STATUS_EOF;
        }

        if ($intOff < 0) {
            $intOff = $this->_fileoff;
        }

        if ($this->_status != self::READ_STATUS_MORE) {
            return $this->_status;
        }

        $strVal = file_get_contents(
            $this->_filename, false, null, $intOff,
            min(self::READ_BUFFER, $this->_filename - $intOff)
        );
        $this->_iotimes++;

        if ($strVal === false) {
            return self::READ_STATUS_ERROR;
        }

        $intLen = strlen($strVal);
        $this->_caches  .= $strVal;
        $this->_fileoff += $intLen;

        return ($intLen < self::READ_BUFFER) ? self::READ_STATUS_EOF : self::READ_STATUS_MORE;
    }
    /* }}} */

    /* {{{ private Mixture  _row_filter() */
    /**
     * 过滤切割后的行
     *
     * @access private
     * @param  Mixture $mixVal
     * @return Mixture
     */
    private function _row_filter($mixVal)
    {
        if ((int)$this->_intCol < 1) {
            return $mixVal;
        }

        $mixRet = array();
        foreach ((array)$mixVal AS $arrRow) {
            if (!is_array($arrRow) || count($arrRow) != $this->_intCol) {
                self::_error(701, sprintf('Error line "%s"', implode('\\t', (array)$arrRow)));
                continue;
            }
            $mixRet[] = $arrRow;
        }

        return $mixRet;
    }
    /* }}} */

}

interface TxtRead_Parser_Interface
{

    /**
     * 切割一块缓冲数据
     *
     * @access public
     * @param  String  $strVal (refferrence)
     * @param  Boolean $bolAll : 是否最后一次数据
     * @return Mixture
     */
    public function explode(&$strVal, $bolAll = false);

}

class TxtRead_Parser_Default implements TxtRead_Parser_Interface
{

    /* {{{ 成员变量 */

    /**
     * @ 列分隔符
     */
    private $_strColSeg;

    /**
     * @ 行分隔符
     */
    private $_strEolSeg;

    /**
     * @ 忽略空列
     */
    private $_bolIgnWht;

    /* }}} */

    /* {{{ public Boolean __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return Boolean true or false
     */
    public function __construct($col = "\t", $eol = "\n", $ignwht = false)
    {
        $this->_strColSeg   = $col;
        $this->_strEolSeg   = $eol;
        $this->_bolIgnWht   = (bool)$ignwht;

        return true;
    }
    /* }}} */

    /* {{{ public Mixture explode() */
    /**
     * 切割一块缓冲数据
     *
     * @access public
     * @param  String  $strVal (refferrence)
     * @param  Boolean $bolAll : 是否最后一次数据
     * @return Mixture
     */
    public function explode(&$strVal, $bolAll = false)
    {
        $mixRet = array();
        if (!is_scalar($strVal)) {
            return false;
        }

        if (!isset($this->_strColSeg[0]) || !isset($this->_strEolSeg[0])) {
            return false;
        }

        if ($bolAll !== true) {
            $intPos = strrpos($strVal, $this->_strEolSeg);
            if ($intPos === false) {
                return array();
            }
            $mixVal = explode($this->_strEolSeg, substr($strVal, 0, $intPos + 1));
            $strVal = (string)substr($strVal, $intPos + 1);
        } else {
            $mixVal = explode($this->_strEolSeg, $strVal);
            $strVal = '';
        }

        if (!is_array($mixVal)) {
            return false;
        }

        foreach ($mixVal AS $strTmp) {
            if (empty($strTmp)) {
                continue;
            }
            $mixTmp = explode($this->_strColSeg, $strTmp);
            if (!is_array($mixTmp)) {
                continue;
            }
            $mixTmp = array_map('trim', $mixTmp);
            if ($this->_bolIgnWht === true) {
                $mixTmp = array_values(array_filter($mixTmp, 'strlen'));
            }
            $mixRet[]   = $mixTmp;
        }

        return $mixRet;
    }
    /* }}} */

}

