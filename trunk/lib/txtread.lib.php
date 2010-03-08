<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | �ı���ȡ��															|
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

    /* {{{ ���� */

    /**
     * @��ȡ����
     */
    const READ_BUFFER   = 10485760;       /**< 10M       */

    /**
     * @��ȡ������
     */
    const READ_STATUS_ERROR = 0;          /**<  ������      */
    const READ_STATUS_MORE  = 1;          /**<  ����      */
    const READ_STATUS_EOF   = 2;          /**<  ���      */

    /* }}} */

    /* {{{ ��Ա���� */

    /**
     * @�ļ���
     */
    private $_filename;

    /**
     * @��С
     */
    private $_filesize;

    /**
     * @�ļ��α�
     */
    private $_fileoff;

    /**
     * @�����α�
     */
    private $_readidx;

    /**
     * @��ȡ״̬
     */
    private $_status    = self::READ_STATUS_EOF;

    /**
     * @���ݻ���
     */
    private $_caches;

    /**
     * @�����صĶ�ά����
     */
    private $_return    = array();

    /**
     * @��ӳ���
     */
    private $_arrMap;

    /**
     * @����
     */
    private $_intCol;

    /**
     * @IO����
     */
    private $_iotimes   = 0;

    /* }}} */

    /* {{{ public Boolean __construct() */
    /**
     * ���캯��
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
     * ������󻺴�
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
     * �����ļ���
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
     * ���ý�������
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
     * �����е�ӳ�����
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
     * ȡ������
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
     * ��ȡ�ļ�����
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
     * �����и�����
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
     * �и�һ�黺������
     *
     * @access public
     * @param  String  $strVal (refferrence)
     * @param  Boolean $bolAll : �Ƿ����һ������
     * @return Mixture
     */
    public function explode(&$strVal, $bolAll = false);

}

class TxtRead_Parser_Default implements TxtRead_Parser_Interface
{

    /* {{{ ��Ա���� */

    /**
     * @ �зָ���
     */
    private $_strColSeg;

    /**
     * @ �зָ���
     */
    private $_strEolSeg;

    /**
     * @ ���Կ���
     */
    private $_bolIgnWht;

    /* }}} */

    /* {{{ public Boolean __construct() */
    /**
     * ���캯��
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
     * �и�һ�黺������
     *
     * @access public
     * @param  String  $strVal (refferrence)
     * @param  Boolean $bolAll : �Ƿ����һ������
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

