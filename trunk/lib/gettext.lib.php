<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 国际化语言处理包														|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Aleafs. All Rights Reserved							|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id$

if (!class_exists('HA_Single')) {
    require_once(sprintf('%s/single.lib.php', dirname(__FILE__)));
}

class HA_Gettext extends HA_Single
{

    /* {{{ 静态变量 */

    /**
     * @是否启用缓存
     */
    private static $_cached = false;

    /**
     * @语言域
     */
    private static $_domain = array();

    /* }}} */

    /* {{{ 成员变量 */

    /**
     * @各域状态
     */
    private $_status = array();

    /**
     * @缓存数据
     */
    private $_caches = array();

    /* }}} */

    /* {{{ public Object  instance() */
    /**
     * 获取一个日志实例
     *
     * @access public static
     * @return Object
     */
    public static function instance($mixArg = null, $strCls = null)
    {
        return parent::instance($mixArg, is_null($strCls) ? __CLASS__ : $strCls);
    }
    /* }}} */

    /* {{{ public Boolean __destruct() */
    /**
     * 析构函数
     *
     * @access public
     * @return Boolean true or false
     */
    public function __destruct()
    {
        return parent::__destruct();
    }
    /* }}} */

    /* {{{ public Boolean bind() */
    /**
     * 绑定域路径
     *
     * @access public static
     * @param  String $domain
     * @param  String $path
     * @return Boolean true or false
     */
    public static function bind($domain, $path)
    {
        $domain = strtolower(trim($domain));
        if (isset(self::$_domain[$domain])) {
            self::_error(300, sprintf('language domain "%s" already exists', $domain));
            return false;
        }

        self::$_domain[$domain] = self::realpath($path);
        return true;
    }
    /* }}} */

    /* {{{ public String  translate() */
    /**
     * 翻译一个字符串
     *
     * @access public
     * @param  String $string
     * @param  String $domain (default null)
     * @return String
     */
    public function translate($string, $domain = null)
    {
        if ($domain === null) {
            $arrAll = array_reverse(array_keys(self::$_domain));
        } else {
            $arrAll = array($domain);
        }

        foreach ($arrAll AS $domain) {
            if ($this->_translate($string, $domain) !== false) {
                return $string;
            }
        }

        return $string;
    }
    /* }}} */

    /* {{{ private Boolean _translate() */
    /**
     * 从固定的域翻译
     *
     * @access private
     * @param  String $string (refferrence)
     * @param  String $domain
     * @return Boolean true or false
     */
    private function _translate(&$string, $domain)
    {
        $status = &$this->_status[$domain];
        if ($status['error'] > 0) {
            return false;
        }

        if (empty($status) && $this->_load_mofile($domain) != true) {
            return false;
        }

        if (self::$_cached == true) {
            if (!isset($this->_caches[$domain][$string])) {
                return false;
            }
            $string = $this->_caches[$domain][$string];
            return true;
        }

        $intIdx = $this->_find($string, $domain);
        if ($intIdx < 0) {
            return false;
        }
        $string = $this->_get_translate_string($intIdx, $domain);

        return true;
    }
    /* }}} */

    /* {{{ private Boolean _load_mofile() */
    /**
     * 加载一个MO文件
     *
     * @access private
     * @param  String $domain
     * @return Boolean true or false
     */
    private function _load_mofile($domain)
    {
        $domain = strtolower(trim($domain));
        $status = &$this->_status[$domain];
        $status['error'] = 1;
        if (!isset(self::$_domain[$domain])) {
            self::_error(301, sprintf('undefined domain "%s"', $domain));
            return false;
        }

        $mofile = sprintf('%s%s%s%s.mo',
            self::$_domain[$domain], $domain,
            strlen($domain) > 0 ? '-' : '',
            $this->_strIdx
        );
        if (!is_file($mofile)) {
            self::_error(302, sprintf('MO file "%s" not exists', $mofile));
            return false;
        }

        $header = $this->_parse_head($this->_read_file($mofile, 20, 0));
        if (!is_array($header)) {
            self::_error(304, sprintf('Wrong format for MO file "%s"', $mofile));
            return false;
        }

        $unpack = $header['pack'] . $header['count'];
        $intAll = 8 * $header['count'];
        $arrTb1 = unpack($unpack, $this->_read_file($mofile, $intAll, $header['off_o']));
        $arrTb2 = unpack($unpack, $this->_read_file($mofile, $intAll, $header['off_t']));

        $status = array_merge($status, $header);
        $status['error']    = 0;
        $status['mofile']   = $mofile;
        $status['tab_o']    = $arrTb1;
        $status['tab_t']    = $arrTb2;
        if (self::$_cached === true) {
            $arrMap = array();
            for ($intIdx = 0; $intIdx < $header['count']; $intIdx++) {
                $strKey = $this->_get_original_string($intIdx, $domain);
                $strVal = $this->_get_translate_string($intIdx, $domain);
                $arrMap[$strKey] = $strVal;
            }
            $this->_caches[$domain] = $arrMap;
        }

        return true;
    }
    /* }}} */

    /* {{{ private Mixture _parse_head() */
    /**
     * 读取MO文件头信息
     *
     * @access private
     * @param  String $string
     * @return Mixture
     */
    private function _parse_head($string)
    {
        $intVal = array_shift(unpack('V', substr($string, 0, 4)));

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

        $arrVal = unpack(sprintf('%s4', $strVal), substr($string, 4));
        $arrRet = array(
            'pack'  => $strVal,
            'rev'   => $arrVal[1],
            'count' => $arrVal[2],
            'off_o' => $arrVal[3],
            'off_t' => $arrVal[4],
        );

        return $arrRet;
    }
    /* }}} */

    /* {{{ private String  _read_file() */
    /**
     * 读取文件
     *
     * @access private
     * @param  String $file
     * @param  Interger $intLen
     * @param  Interger $intOff
     * @return String
     */
    private function _read_file($file, $intLen, $intOff)
    {
        if (!is_file($file)) {
            return '';
        }

        $intMax = filesize($file);
        $intLen = (int)$intLen;
        $intOff = (int)$intOff;
        if ($intOff >= $intMax || $intLen < 1) {
            return '';
        }

        return file_get_contents(
            $file, false, null, $intOff,
            min($intLen, $intMax - $intOff)
        );
    }
    /* }}} */

    /* {{{ private String  _get_original_string() */
    /**
     * 读取原始字符串
     *
     * @access private
     * @param Interger $intIdx
     * @param String $domain
     * @return String
     */
    private function _get_original_string($intIdx, $domain)
    {
        $status = $this->_status[$domain];
        $intIdx = (int)$intIdx * 2;
        $intOff = (int)$status['tab_o'][$intIdx + 2];
        $intLen = (int)$status['tab_o'][$intIdx + 1];
        if ($intLen < 1) {
            return '';
        }

        return $this->_read_file($status['mofile'], $intLen, $intOff);
    }
    /* }}} */

    /* {{{ private String  _get_translate_string() */
    /**
     * 读取翻译字符串
     *
     * @access private
     * @param  Interger $intIdx
     * @param String $domain
     * @return String
     */
    private function _get_translate_string($intIdx, $domain)
    {
        $status = $this->_status[$domain];
        $intIdx = (int)$intIdx * 2;
        $intOff = (int)$status['tab_t'][$intIdx + 2];
        $intLen = (int)$status['tab_t'][$intIdx + 1];
        if ($intLen < 1) {
            return '';
        }

        return $this->_read_file($status['mofile'], $intLen, $intOff);
    }
    /* }}} */

    /* {{{ private Interger _find() */
    /**
     * 二分查找字符串
     *
     * @access private
     * @param  String $string
     * @param  String $domain
     * @return Interger
     */
    private function _find($string, $domain, $intBeg = -1, $intEnd = -1)
    {
        $arrOpt = $this->_status[$domain];
        if ($intBeg < 0 || $intEnd < 0) {
            $intBeg = 0;
            $intEnd = $arrOpt['count'];
        }

        if (abs($intEnd - $intBeg) <= 1) {
            if (strcmp($string, $this->_get_original_string($intBeg, $domain)) == 0) {
                return $intBeg;
            }

            return -1;
        }

        if ($intBeg > $intEnd) {
            return $this->_find($string, $domain, $intEnd, $intBeg);
        }

        $intIdx = (int)(($intBeg + $intEnd) / 2);
        $intCmp = strcmp($string, $this->_get_original_string($intIdx, $domain));
        if ($intCmp > 0) {
            return $this->_find($string, $domain, $intIdx, $intEnd);
        }

        if ($intCmp < 0) {
            return $this->_find($string, $domain, $intBeg, $intIdx);
        }

        return $intIdx;
    }
    /* }}} */

}

