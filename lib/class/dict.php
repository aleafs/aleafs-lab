<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 字典类,KEY => VALUE 本地存储结构		    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 aleafs.com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: dict.php 22 2010-04-15 16:28:45Z zhangxc83 $

namespace Aleafs\Lib;

class Dict
{

    /* {{{ 静态常量 */

    const DICT_TAGNAME  = 'RBD';
    const DICT_VERSION  = 100;
    const DICT_HASH_PRI = 5381;
    const MAX_KEY_LEN   = 0xFF;
    const MIN_GZIP_LEN  = 1024;

    const TYPE_SCALAR   = 2;
    const TYPE_ARRAY    = 4;
    const TYPE_OBJECT   = 8;

    /* }}} */

    /* {{{ 成员变量 */

    private $dfile  = '';

    private $fsize  = 0;

    private $version;

    private $prime;

    private $bucket;

    private $locked;

    private $compress   = false;

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct($file, $bucket = 0)
    {
        if (!is_file($file) && !self::create($file, (int)$bucket)) {
            throw new \Aleafs\Lib\Exception(sprintf(
                'No such dict file as "%s", create failed', $file
            ));
        }

        $this->dfile    = realpath($file);
        if (false === $this->init()) {
            throw new \Aleafs\Lib\Exception('Dict head check failed');
        }
        $this->compress = function_exists('gzcompress') ? true : false;
    }
    /* }}} */

    /* {{{ public Mixture get() */
    /**
     * 获取记录
     *
     * @access public
     * @return Mixture
     */
    public function get($key)
    {
        $key = trim($key);
        $len = strlen($key);
        if (0 == $len || $len > self::MAX_KEY_LEN) {
            return false;
        }

        $rec = $this->find($key);
    }
    /* }}} */

    /* {{{ public Boolean set() */
    /**
     * 添加/更新键值对
     *
     * @access public
     * @return Boolean true or false
     */
    public function set($key, $value)
    {
        $key = trim($key);
        $len = strlen($key);
        if (0 == $len || $len > self::MAX_KEY_LEN) {
            return false;
        }
    }
    /* }}} */

    public function reset()
    {
    }

    public function next()
    {
    }

    public function lock()
    {
    }

    public function optimize()
    {
    }

    /* {{{ private static Boolean create() */
    /**
     * 写入头信息
     *
     * @access private static
     * @param  string  $file
     * @param  integer $bucket
     * @return Boolean true or false
     */
    private static function create($file, $bucket = 0)
    {
        $dr = dirname($file);
        if (!is_dir($dr) && !mkdir($dr, 0755, true)) {
            return false;
        }

        return (bool)file_put_contents($file, pack(
            'a3IIIIIa9', self::DICT_TAGNAME, self::DICT_VERSION,
            max(0, (int)$bucket), self::DICT_HASH_PRI, 32, 0, ''
        ), LOCK_EX);
    }
    /* }}} */

    /* {{{ private void init() */
    /**
     * 初始化字典
     *
     * @access private
     * @return void
     */
    private function init()
    {
        $os = $this->read(0, 32);
        if (32 != strlen($os)) {
            return false;
        }

        $os = unpack('a3tag/Iver/Ibucket/Iprime/Ifsize/Ilock/a9reversed', $os);
        if (0 !== strcasecmp(self::DICT_TAGNAME, $os['tag'])) {
            return false;
        }

        if ($this->fsize != $os['fsize']) {
            return false;
        }

        $this->version  = $os['ver'];
        $this->bucket   = $os['bucket'];
        $this->prime    = $os['prime'];
        $this->locked   = $os['lock'];
    }
    /* }}} */

    /* {{{ private static string pack() */
    /**
     * 数据打包
     *
     * @access private static
     * @return string
     */
    private static function pack($data)
    {
        if (is_scalar($data)) {
            $type   = self::TYPE_SCALAR;
        } elseif (is_array($data)) {
            $data   = json_encode($data);
            $type   = self::TYPE_ARRAY;
        } else {
            $data   = json_encode($data);
            $type   = self::TYPE_OBJECT;
        }

        if ($this->compress && strlen($data) > self::MIN_GZIP_LEN) {
            $data   = gzcompress($data);
            $type   |= 1;
        }

        return pack('I', $type) . $data;
    }
    /* }}} */

    /* {{{ private static Mixture unpack() */
    /**
     * 数据解包
     *
     * @access private static
     * @return Mixture
     */
    private static function unpack($data)
    {
        if (strlen($data) < 4) {
            return false;
        }

        $type   = unpack('I', substr($data, 0, 4));
        $type   = reset($type);
        $data   = substr($data, 4);

        if ($type = ($type & 0x0001) > 0) {
            $data   = gzuncompress($data);
        }

        if (self::TYPE_ARRAY == $type) {
            return json_decode($data, true);
        }

        if (self::TYPE_OBJECT == $type) {
            return json_decode($data);
        }

        return $data;
    }
    /* }}} */

    /* {{{ private string read() */
    /**
     * 读取文件
     *
     * @access private
     * @return string
     */
    private function read($off, $len)
    {
        if (empty($this->fsize)) {
            $this->fsize = filesize($this->dfile);
        }

        return file_get_contents(
            $this->dfile, false, null,
            $off = ($off < 1) ? 0 : (int)$off,
            min(max(0, (int)$len), $this->fsize - $off)
        );
    }
    /* }}} */

    /* {{{ private integer hash() */
    /**
     * 获取KEY的hash值
     *
     * @access private
     * @return Integer
     */
    private function hash($key)
    {
        if ($this->bucket < 1) {
            return 0;
        }

        $si = $this->prime;
        // time33 算法
        for ($i = 0, $len = strlen($key); $i < $len; $i++) {
            $si = ($si << 5) + $si + ord(substr($key, $i, 1));
        }

        return $si % $this->bucket;
    }
    /* }}} */

    /* {{{ private Mixture find() */
    /**
     * 根据KEY找所在记录
     *
     * @access private
     * @return Mixture
     */
    private function find($key)
    {
    }
    /* }}} */

}

