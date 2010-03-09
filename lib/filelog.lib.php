<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +-------------------------------------------------------------+
// | 文件日志类                		   		  				     |
// +-------------------------------------------------------------+
// | Author: Zhang Xuancheng <zhangxuancheng@baidu.com>          |
// +-------------------------------------------------------------+
//
// $Id$
//

if (!class_exists('HA_Single')) {
    require_once(sprintf('%s/single.lib.php', dirname(__FILE__)));
}

class HA_Filelog extends HA_Single
{

    /* {{{ 静态常量 */

    const LOG_MAX_FILESIZE  = 2143289000;

    const LOG_LEVEL_NONE    = 0;
    const LOG_LEVEL_FATAL   = 1;
    const LOG_LEVEL_ERROR   = 2;
    const LOG_LEVEL_WARN    = 4;
    const LOG_LEVEL_NOTICE  = 8;
    const LOG_LEVEL_TRACE   = 16;
    const LOG_LEVEL_DEBUG   = 128;
    const LOG_LEVEL_ALL     = 255;

    const LOG_ROTATE_SIZE   = 1;
    const LOG_ROTATE_DATE   = 2;
    const LOG_ROTATE_HOUR   = 3;

    /* }}} */

    /* {{{ 静态变量 */

    /**
     * @过滤的字符
     */
    private static $_arrEscapeChar = array(
        "\r"    => "\\r",
        "\n"    => "\\n",
    );

    /**
     * @日志级别描述
     */
    private static $_arrLogLevel = array(
        self::LOG_LEVEL_FATAL => 'FATAL',
        self::LOG_LEVEL_ERROR => 'ERROR',
        self::LOG_LEVEL_WARN  => 'WARNN',
        self::LOG_LEVEL_NOTICE=> 'NOTICE',
        self::LOG_LEVEL_TRACE => 'TRACE',
        self::LOG_LEVEL_DEBUG => 'DEBUG',
    );

    /* }}} */

    /* {{{ 成员变量 */

    /**
     * @日志缓存
     */
    private $_strLogCache;

    /**
     * @最大缓存数
     */
    private $_intMaxCache;

    /**
     * @日志级别
     */
    private $_intLogLevel;

    /**
     * @日志得分(便于监控使用)
     */
    private $_intLogEvent;

    /**
     * @日志切割方式
     */
    private $_intRotate;

    /**
     * @操作人
     */
    private $_intUserId  = '-';

    /**
     * @日志存放路径
     */
    private $_strLogPath = './';

    /**
     * @日志文件名
     */
    private $_strLogName = 'default';

    /**
     * @日志扩展名
     */
    private $_strLogExt  = 'php';

    /* }}} */

    /* {{{ public static  instance() */
    /**
     * 获取一个日志实例
     *
     * @access public static
     * @param  String $strLogName (default null)
     * @return Object
     */
    public static function instance($mixArg = null, $strCls = null)
    {
        return parent::instance($mixArg, is_null($strCls) ? __CLASS__ : $strCls);
    }
    /* }}} */

    /* {{{ public Object logfile() */
    /**
     * 设置日志文件名
     *
     * @access public
     * @param  String $strFile
     * @param  String $strDir
     * @return Object $this
     */
    public function logfile($strFile, $strDir)
    {
        $strDir = empty($strDir) ? '.' : $strDir;
        $strAll = self::realpath($strDir . '/' . $strFile);
        $this->_strLogPath = dirname($strAll);

        $arrExt = array_filter(explode('.', basename($strAll)), 'strlen');
        switch (count($arrExt)) {
        case 1:
            $this->_strLogName = implode('.', $arrExt);
            break;
        case 0:
            break;
        default:
            $this->_strLogExt  = strtolower(array_pop($arrExt));
            $this->_strLogName = implode('.', $arrExt);
            break;
        }

        return $this;
    }
    /* }}} */

    /* {{{ public Object level() */
    /**
     * 设置日志记录级别
     *
     * @access public
     * @param  Integer $intLogLevel
     * @return Object true or false
     */
    public function level($intLogLevel)
    {
        $this->_intLogLevel	= (int)$intLogLevel;
        return $this;
    }
    /* }}} */

    /* {{{ public Object rotate() */
    /**
     * 设置日志切割方式
     *
     * @access public
     * @param  Integer $intRotate
     * @return Object $this
     */
    public function rotate($intRotate)
    {
        $intRotate = intval($intRotate);
        if (in_array($intRotate, array(self::LOG_ROTATE_HOUR, self::LOG_ROTATE_DATE, self::LOG_ROTATE_SIZE))) {
            $this->_intRotate = $intRotate;
        }
        return $this;
    }
    /* }}} */

    /* {{{ public Object cache() */
    /**
     * 设置最大缓存字节数
     *
     * @access public
     * @param  Integer $intMaxSize
     * @return Object true
     */
    public function cache($intMaxSize)
    {
        $this->_intMaxCache = min(intval($intMaxSize), 41943040);
        return $this;
    }
    /* }}} */

    /* {{{ public Object userid() */
    /**
     * 设置用户ID
     *
     * @access public
     * @param  Integer $intUid
     * @return Object $this
     */
    public function userid($intUid)
    {
        $this->_intUserId = (int)$intUid;
        return $this;
    }
    /* }}} */

    /* {{{ public Boolean write() */
    /**
     * 日志写入类
     *
     * @access public
     * @return Boolean true or false
     */
    public function write()
    {
        $arrArg = func_get_args();
        $intLvl = (int)array_shift($arrArg);
        $bolErr = false;
        if (($intLvl & self::LOG_LEVEL_FATAL) > 0) {
            $this->_intLogEvent += 500;     //error 日志会增加一倍
            $bolErr = true;
        }
        if (($intLvl & self::LOG_LEVEL_ERROR) > 0) {
            $this->_intLogEvent += 50;
            $bolErr = true;
        }
        if (($intLvl & self::LOG_LEVEL_WARN) > 0) {
            $this->_intLogEvent += 5;
            $bolErr = true;
        }

        if (($intLvl & $this->_intLogLevel) < 1) {
            return true;
        }

        $this->_strLogCache.= $this->_build($intLvl, (array)$arrArg) . "\n";
        if (strlen($this->_strLogCache) >= $this->_intMaxCache) {
            return $this->_write($bolErr);
        }

        return true;
    }
    /* }}} */

    /* {{{ public Boolean __destruct() */
    /**
     * 析构函数
     *
     * @access public
     * @return Boolean true
     */
    public function __destruct()
    {
        $this->_write();
        parent::__destruct();

        return true;
    }
    /* }}} */

    /* {{{ protected Boolean _log() */
    /**
     * 写入日志记录
     *
     * @access protected static
     * @param  String  $strTag
     * @param  Mixtrue $mixVAL    (default null)
     * @param  Integer $intLvl (default LOG_LEVEL_NOTICE)
     * @return Boolean true  or false
     */
    protected static function _log($strTag, $mixVal = null, $intLvl = null)
    {
        return true;
    }
    /* }}} */

    /* {{{ protected Object  _options() */
    /**
     * 设置对象属性
     *
     * @access protected
     * @param  Mixture $mixArg (default null)
     * @return Object $this
     */
    protected function _options($mixArg = null)
    {
        $this->_intLogLevel = self::LOG_LEVEL_ALL & (~self::LOG_LEVEL_DEBUG);
        $this->_intRotate   = self::LOG_ROTATE_DATE;
        $this->_intMaxCache = 0;
        $this->_intLogEvent = 0;
        $this->_strLogCache = '';

        return $this;
    }
    /* }}} */

    /* {{{ private String  _file() */
    /**
     * 获取日志文件名
     *
     * @access private
     * @param  Boolean $bolGen (default false)
     * @return String
     */
    private function _file($bolGen = false)
    {
        if (!is_string($this->_strIdx)) {         /**<  打印到屏幕      */
            return false;
        }

        $strFileName = $this->_strLogPath . '/';
        $intCurrDate = date('Ymd');
        $intCurrHour = date('H');
        switch($this->_intRotate) {
        case self::LOG_ROTATE_HOUR:
            $strPathExt = sprintf('%s/',    $intCurrDate);
            $strFileExt = sprintf('.%s-%s', $intCurrDate, $intCurrHour);
            break;
        case self::LOG_ROTATE_DATE:
            $strPathExt = sprintf('%s/',    $intCurrDate);
            $strFileExt = sprintf('.%s',    $intCurrDate);
            break;
        default:
            $strPathExt = '';
            $strFileExt = '';
            break;
        }
        $strFileName = self::realpath($strFileName . sprintf(
            '/%s/%s%s.%s',
            $strPathExt, $this->_strLogName, $strFileExt, $this->_strLogExt
        ));

        if (is_file($strFileName) &&   /* <备份已满日志> */
            filesize($strFileName) > self::LOG_MAX_FILESIZE - strlen($this->_strLogCache))
        {
            for ($i = 1; $i < 100; $i++)
            {
                $strTempName = $strFileName . '.' . $i;
                if (file_exists($strTempName)) {
                    continue;
                }
                rename($strFileName, $strTempName);
                break;
            }

            return $strFileName;
        }

        if ($bolGen === true) {               /* <检查并创建目录> */
            $strTempName = dirname($strFileName);
            if (!is_dir($strTempName) && !@mkdir($strTempName, 0777, true)) {
                //nothing
            }
        }

        return $strFileName;
    }
    /* }}} */

    /* {{{ private Boolean _write() */
    /**
     * 将缓存写入文件
     *
     * @access protected
     * @param  Boolean $bolErr (default false)
     * @return Boolean true or false
     */
    private function _write($bolErr = false)
    {
        $strLog = $this->_file(true);
        if (!is_string($strLog)) {
            echo $this->_strLogCache;
            $this->_strLogCache = '';
            return true;
        }

        if (!file_put_contents($strLog, $this->_strLogCache, FILE_APPEND)) {
            return false;
        }

        if ($bolErr === true) {
            file_put_contents($strLog . '.wf', $this->_strLogCache, FILE_APPEND);
        }

        $this->_strLogCache = '';

        return true;
    }
    /* }}} */

    /* {{{ private String  _build() */
    /**
     * 构造一行日志
     *
     * @access protected
     * @param  Integer $intLvl (default LOG_LEVEL_NOTICE)
     * @param  Mixtrue $arrArg (default null)
     * @return String
     */
    private function _build($intLvl, $arrArg)
    {
        $intLvl = (int)$intLvl;
        $strLvl = 'UNKOWN';
        if (isset(self::$_arrLogLevel[$intLvl])) {
            $strLvl = self::$_arrLogLevel[$intLvl];
        } else {
            foreach (self::$_arrLogLevel AS $intKey => $strVar) {
                if (($intKey & $intLvl) > 0) {
                    $strLvl = $strVar;
                    break;
                }
            }
        }

        $strFmt = array_shift($arrArg);
        $strRet = sprintf(
            '%s: [%s] %s %s <%s> %d [*] %s',
            $strLvl, date('Y-m-d\ H:i:s'), self::userip(), $this->_intUserId,
            basename($_SERVER['SCRIPT_FILENAME']), self::$_intPid,
            vsprintf($strFmt, $arrArg)
        );

        return trim(str_replace(array_keys(self::$_arrEscapeChar), self::$_arrEscapeChar, $strRet));
    }
    /* }}} */

    /* {{{ protected static String  _index() */
    /**
     * 构造单例模式对象索引
     *
     * @access protected static
     * @return String
     */
    protected static function _index()
    {
        return 'default';
    }
    /* }}} */

}

