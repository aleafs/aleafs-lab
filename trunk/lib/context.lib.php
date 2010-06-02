<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 上下文环境																|
// +------------------------------------------------------------------------+
// | Copyright (c) 2009 Baidu. Inc. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxuancheng@baidu.com>								|
// +------------------------------------------------------------------------+
//
// $Id: context.lib.php 4 2010-03-09 05:20:36Z zhangxc83 $

if (!class_exists('HA_Single')) {
    require_once(sprintf('%s/single.lib.php', dirname(__FILE__)));
}

class HA_Context extends HA_Single
{

    /* {{{ 静态变量 */

    /**
     * @参数数组
     */
    private static $_arrContext = array();

    /**
     * @属性参数
     */
    private static $_arrOption  = array();

    /**
     * @get_magic_quotes_gpc()
     */
    private static $_magic_gpc  = null;

    /**
     * @aleafs ID
     */
    private static $_aleafs_ID  = null;

    /**
     * @webroot
     */
    private static $_webRoot    = null;

    /**
     * @域名国家后缀
     */
    private static $_arrNation1 = array(
        'ac','ad','ae','af','ag','ai','al','am','an','ao','aq','ar','as','at','au','aw','ax','az',
        'ba','bb','bd','be','bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt','bv','bw','by','bz',
        'ca','cc','cd','cf','cg','ch','ci','ck','cl','cm','cn','co','cr','cu','cv','cx','cy','cz',
        'de','dj','dk','dm','do','dz',
        'ec','ee','eg','er','es','et','eu',
        'fi','fj','fk','fm','fo','fr',
        'ga','gb','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp','gq','gr','gs','gt','gu','gw','gy',
        'hk','hm','hn','hr','ht','hu',
        'id','ie','il','im','in','io','iq','ir','is','it',
        'je','jm','jo','jp',
        'ke','kg','kh','ki','km','kn','kp','kr','kw','ky','kz',
        'la','lb','lc','li','lk','lr','ls','lt','lu','lv','ly',
        'ma','mc','md','me','mg','mh','mk','ml','mm','mn','mo','mp','mq','mr','ms','mt','mu','mv','mw','mx','my','mz',
        'na','nc','ne','nf','ng','ni','nl','no','np','nr','nu','nz',
        'om',
        'pa','pe','pf','pg','ph','pk','pl','pm','pn','pr','ps','pt','pw','py',
        'qa',
        're','ro','rs','ru','rw',
        'sa','sb','sc','sd','se','sg','sh','si','sj','sk','sl','sm','sn','so','sr','st','su','sv','sy','sz',
        'tc','td','tf','tg','th','tj','tk','tl','tm','tn','to','tp','tr','tt','tv','tw','tz',
        'ua','ug','uk','us','uy','uz',
        'va','vc','ve','vg','vi','vn','vu',
        'wf','ws',
        'ye','yt','yu',
        'za','zm','zw',
    );

    /**
     * @国家域名二级后缀
     */
    private static $_arrNation2 = array(
        'cn' => 'ac|ah|bj|cq|fj|gd|gs|gz|gx|ha|hb|he|hi|hl|hn|jl|js|jx|ln|nm|nx|qh|sc|sd|sh|sn|sx|tj|tw|xj|xz|yn|zj',
        'tw' => 'mil|idv|game|ebiz|club',
        'hk' => 'idv',
        'jp' => 'ac|ad|co|ed|go|gr|lg|ne|or',
        'kr' => 'co|ne|or|re|pe|go|mil|ac|hs|ms|es|sc|kg|seoul|busan|daegu|incheon|gwangju|daejeon|ulsan|gyeonggi|gangwon|chungbuk|chungnam|jeonbuk|jeonnam|gyeongbuk|gyeongnam|jeju',
        'uk' => 'ac|co|gov|ltd|me|mod|net|nhs|nic|org|parliament|plc|police|sch',
        'nz' => 'ac|co|geek|gen|maori|net|org|school|cri|govt|iwi|parliament|mil',
        'il' => 'ac|co|org|net|k12|gov|muni|idf',
    );

    /* }}} */

    /* {{{ public static Object instance() */
    /**
     * 获取单例对象的一个实例
     *
     * @access public static
     * @param  Mixture $mixArg
     * @param  String  $strCls (default null)
     * @return Object
     */
    public static function instance($mixArg = null, $strCls = null)
    {
        return parent::instance($mixArg, is_null($strCls) ? __CLASS__ : $strCls);
    }
    /* }}} */

    /* {{{ public String referer() */
    /**
     * 获取访问REFER
     *
     * @access public
     * @return String
     */
    public function referer()
    {
        return trim($_SERVER['HTTP_REFERER']);
    }
    /* }}} */

    /* {{{ public String method() */
    /**
     * 获取请求方法
     *
     * @access public
     * @return String
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'] ? strtoupper(trim($_SERVER['REQUEST_METHOD'])) : null;
    }
    /* }}} */

    /* {{{ public String idname() */
    /**
     * 返回 idname 属性
     *
     * @access public
     * @return String
     */
    public function idname()
    {
        return self::$_aleafs_ID;
    }
    /* }}} */

    /* {{{ public Object setid() */
    /**
     * 设置是否写入idname
     *
     * @access public
     * @param  Boolean $bolSet
     * @return Object $this
     */
    public function setid($bolSet = true)
    {
        self::$_arrOption['idname.create'] = (bool)$bolSet;
        return $this;
    }
    /* }}} */

    /* {{{ public String domain() */
    /**
     * 获取主域名
     *
     * @access public static
     * @param  String $strUrl (default null)
     * @return String
     */
    public static function domain($strUrl = null)
    {
        $arrUrl = (array)parse_url(is_null($strUrl) ? self::webroot() : $strUrl);
        $strUrl = empty($arrUrl['host']) ? $arrUrl['path'] : $arrUrl['host'];
        if (empty($strUrl)) {
            return '';
        }

        $arrUrl = explode('.', strtolower($strUrl));
        $intCnt = count($arrUrl);

        if ($intCnt < 2) {
            return '';
        }

        $strLst = array_pop($arrUrl);
        $arrRet = array($strLst);

        $strTop = implode('|', self::$_arrNation1);
        $arrKey = self::$_arrNation2;
        if (!preg_match(sprintf('/(%s)/', $strTop), $strLst)) {
            $arrRet[] = array_pop($arrUrl);
        } else {
            $strTmp = array_pop($arrUrl);
            $arrRet[] = $strTmp;
            if (preg_match('/(com|net|org|gov|edu|co)/', $strTmp) || 
                (!empty($arrKey[$strLst]) && preg_match(sprintf('/(%s)/', $arrKey[$strLst]), $strTmp) ))
            {
                $strTmp = array_pop($arrUrl);
                if (strlen($strTmp) > 0 && $strTmp != 'www') {
                    $arrRet[] = $strTmp;
                }
            }
        }

        return implode('.', array_reverse($arrRet));
    }
    /* }}} */

    /* {{{ public String webroot() */
    /**
     * 获取 webroot
     *
     * @access public
     * @return String
     */
    public function webroot()
    {
        if (is_null(self::$_webRoot)) {
            $strRet = sprintf(
                '%s://%s%s',
                $_SERVER['HTTPS'] ? 'https' : 'http',
                trim($_SERVER['HTTP_HOST']),
                dirname(trim($_SERVER['SCRIPT_FILENAME']))
            );

            if (defined('APP_ROOT_DIR')) {
                foreach (explode('/', APP_ROOT_DIR) AS $strDir) {
                    if (strcmp(trim($strDir), '..') != 0) {
                        continue;
                    }
                    $strRet = substr($strRet, 0, strrpos($strRet, '/'));
                }
            }
            self::$_webRoot = $strRet;
        }

        return self::$_webRoot;
    }
    /* }}} */

    /* {{{ public String session() */
    /**
     * 获取session中的信息
     *
     * @access public
     * @param  String $strKey
     * @param  String $default
     * @return String
     */
    public function session($strKey, $default = null)
    {
        return isset($_SESSION[$strKey]) ? $_SESSION[$strKey] : $default;
    }
    /* }}} */

    /* {{{ public String cookie() */
    /**
     * 获取cookie中的信息
     *
     * @access public
     * @param  String $strKey
     * @param  String $default
     * @return String
     */
    public function cookie($strKey, $default = null)
    {
        return isset($_COOKIE[$strKey]) ? $_COOKIE[$strKey] : $default;
    }
    /* }}} */

    /* {{{ public String script() */
    /**
     * 返回当前执行脚本名称
     *
     * @access public
     * @return String
     */
    public function script()
    {
        return realpath($_SERVER['SCRIPT_FILENAME']);
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 获取内容
     *
     * @access public
     * @param  String $strKey
     * @param  Mixture $default (default null)
     * @return Mixture
     */
    public function get($strKey, $default = null)
    {
        return isset(self::$_arrContext[$strKey]) ? self::$_arrContext[$strKey] : $default;
    }
    /* }}} */

    /* {{{ public Mixture __get() */
    /**
     * 访问一个私有变量的方法
     *
     * @access protected
     * @param  String  $strKey
     * @return Mixture
     */
    public function __get($strKey)
    {
        return $this->get($strKey);
    }
    /* }}} */

    /* {{{ public Mixture strip() */
    /**
     * 过滤转义后的字符串
     *
     * @access public static
     * @param  Mixture $mixVal
     * @return Mixture
     */
    public static function strip($mixVal)
    {
        if (is_null(self::$_magic_gpc)) {
            self::$_magic_gpc = get_magic_quotes_gpc() ? true : false;
        }
        if (self::$_magic_gpc == false) {
            return $mixVal;
        }

        if (is_scalar($mixVal)) {
            return stripslashes($mixVal);
        }

        $mixRet = array();
        foreach ((array)$mixVal AS $key => $val) {
            $mixRet[self::strip($key)] = self::strip($val);
        }

        return $mixRet;
    }
    /* }}} */

    /* {{{ protected Object _options() */
    /**
     * 设置对象属性
     *
     * @access protected
     * @param  Mixture $mixArg (default null)
     * @return Object $this
     */
    protected function _options($mixArg = null)
    {
        set_magic_quotes_runtime(0);
        return $this->_init_option($mixArg)->_init_context();
    }
    /* }}} */

    /* {{{ protected String _index() */
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

    /* {{{ private Object  _init_option() */
    /**
     * 初始化option
     *
     * @access private
     * @param  Array $arrOpt
     * @return Object $this
     */
    private function _init_option($arrOpt)
    {
        self::$_arrOption   = array();

        $arrKey = array(
            'idname.cookie' => 'ALEAFSID',
            'idname.domain' => self::domain(),
            'idname.verify' => 'c55ac3ce83eea2182c39132f699a7c83',
            'idname.create' => true,
        );

        $arrOpt = (array)$arrOpt;
        foreach ($arrKey AS $strIdx => $strVal) {
            self::$_arrOption[$strIdx] = empty($arrOpt[$strIdx]) ? $strVal : $arrOpt[$strIdx];
        }

        $idname = $this->cookie(self::$_arrOption['idname.cookie'], '');
        if (self::_idname_verify($idname) == true) {
            self::$_aleafs_ID = $idname;
        } elseif (self::$_arrOption['idname.create'] && $this->method() != null) {

            $strKey = self::$_arrOption['idname.cookie'];
            $strVal = self::_idname_create();
            $strDom = self::$_arrOption['idname.domain'];
            $strDom = strlen($strDom) > 1 ? '.' . $strDom : '';
            $strLog = sprintf('name=%s,value=%s,domain=%s', $strKey, $strVal, $strDom);

            if (setcookie($strKey, $strVal, time() + 31536000, '/', $strDom)) {
                self::_notice('generate cookie [%s]', $strLog);
            } else {
                self::_error(200, sprintf('generate cookie [%s] error', $strLog));
            }
        }

        return $this;
    }
    /* }}} */

    /* {{{ private Object  _init_context() */
    /**
     * 初始化上下文数据
     *
     * @access private
     * @TODO : urldecode ?
     * @return Object $this
     */
    private function _init_context()
    {
        switch ($this->method()) {
        case 'POST':
            self::$_arrContext = self::strip(array_merge($_GET, $_POST));
            break;

        case 'GET':
            self::$_arrContext = self::strip($_GET);
            break;

        case 'PUT':
            parse_str(file_get_contents('php://input'), $arrVal);
            self::$_arrContext = self::strip($arrVal);
            break;

        case null:
            self::$_arrContext = self::_parse_opt();
            break;

        default:
            self::$_arrContext = array();
            break;
        }

        return $this;
    }
    /* }}} */

    /* {{{ private Boolean _idname_verify() */
    /**
     * 校验 idname 是否合法
     *
     * @access private static
     * @param  String $strVal
     * @return Boolean true or false
     */
    private static function _idname_verify($strVal)
    {
        if (strlen($strVal) < 16) {
            return false;
        }

        $strTmp = self::_idname_encode(substr($strVal, 8) . self::$_arrOption['idname.verify']);
        if (strcasecmp(substr($strVal, 0, 8), sprintf('%08x', $strTmp)) != 0) {
            return false;
        }

        return true;
    }
    /* }}} */

    /* {{{ private String  _idname_create() */
    /**
     * 生成 idname 
     *
     * @access private static
     * @return String
     */
    private static function _idname_create()
    {
        $arrVal = array(time(), self::userip(true), self::$_intPid, rand());
        $strRet = '';
        foreach ($arrVal AS $intVal) {
            $strRet .= sprintf('%x', $intVal);
        }
        $strRet = substr($strRet, 0, 24);
        $strRet = sprintf(
            '%08x%s',
            self::_idname_encode($strRet . self::$_arrOption['idname.verify']),
            $strRet
        );

        return $strRet;
    }
    /* }}} */

    /* {{{ private Integer _idname_encode() */
    /**
     * 计算 CRC32
     *
     * @access private static
     * @param  String
     * @return Integer
     */
    private static function _idname_encode($strVal)
    {
        $intRet = abs(crc32($strVal));
        if( $intRet & 0x80000000 ) {
            $intRet ^= 0xffffffff;
            $intRet += 1;
        }

        return $intRet;
    }
    /* }}} */

    /* {{{ private Mixture _parse_opt() */
    /**
     * 解析命令行参数
     *
     * @access private static
     * @return Mixture
     */
    private static function _parse_opt()
    {
        if (!is_array($_SERVER['argv'])) {
            return array();
        }

        $arrRet = array();
        for ($i = 1; $i < $_SERVER['argc']; $i++) {
            $val = $_SERVER['argv'][$i];
            if (strncmp($val, '--', 2) != 0) {
                if (strncmp($val, '-', 1) == 0) {
                    $key = substr($val, 1);
                    if (strlen($key) == 0) {
                        continue;
                    }
                    if (strncmp($_SERVER['argv'][$i + 1], '-', 1) != 0) {
                        $val = $_SERVER['argv'][++$i];
                    } else {
                        $val = true;
                    }
                } else {
                    continue;
                }
            } else {
                $val = substr($val, 2);
                $pos = strpos($val, '=');
                if ($pos === false) {
                    $key = $val;
                    $val = true;
                } else {
                    $key = substr($val, 0, $pos);
                    $val = substr($val, $pos + 1);
                }
            }
            $arrRet[$key] = $val;
        }

        return $arrRet;
    }
    /* }}} */

}

