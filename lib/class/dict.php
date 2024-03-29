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

    const DICT_TAGNAME  = 'XDB';
    const DICT_VERSION  = 100;
    const DICT_HASH_PRI = 5381;
    const MAX_KEY_LEN   = 0xEC;
    const MIN_GZIP_LEN  = 1024;

    const TYPE_SCALAR   = 0;
    const TYPE_ARRAY    = 1;
    const TYPE_OBJECT   = 2;

    /* }}} */

    /* {{{ 静态变量 */

    private static $compress;

    /* }}} */

    /* {{{ 成员变量 */

    private $dfile  = '';

    private $fsize  = 0;

    private $version;

    private $prime;

    private $bucket;

    private $locked;

    /* }}} */

    /* {{{ public void __construct() */
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct($file, $bucket = 1)
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

        if (null === self::$compress) {
            self::$compress = function_exists('gzcompress') ? true : false;
        }
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
        $this->fset(12, pack('I', $this->fsize), 4);
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
        if (empty($rec) || empty($rec['vlen']) || !isset($rec['data'])) {
            return false;
        }

        return $this->unpack($rec['data']);
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
        $key    = trim($key);
        $klen   = strlen($key);
        if (0 == $klen || $klen > self::MAX_KEY_LEN) {
            return false;
        }

        $value  = $this->pack($value);
        $vlen   = strlen($value);
        $rec    = $this->find($key);
        if (!empty($rec) && isset($rec['mlen']) && $rec['mlen'] >= $vlen) {
            return $this->fset($rec['off'] + 12, pack('II', $vlen, $rec['mlen']) . $key . $value);
        }

        $value  = pack(
            'IIIII',
            empty($rec['loff']) ? 0 : $rec['loff'],
            empty($rec['roff']) ? 0 : $rec['roff'],
            $klen, $vlen, $vlen
        ) . $key . $value;
        $vlen   = 20 + $vlen + $klen;
        $off    = $this->slab($vlen);
        if (!$this->fset($off, $value, $vlen) || !$this->fset($rec['ptr'], pack('I', $off), 4)) {
            return false;
        }
        $this->fsize += $vlen;

        return true;
    }
    /* }}} */

    /* {{{ public Boolean delete() */
    /**
     * 删除一条记录
     *
     * @access public
     * @return Boolean true or false
     */
    public function delete($key)
    {
        $key    = trim($key);
        $klen   = strlen($key);
        if (0 == $klen || $klen > self::MAX_KEY_LEN) {
            return false;
        }

        $rec    = $this->find($key);
        if (empty($rec) || !isset($rec['off'])) {
            return false;
        }

        if (!$this->fset($rec['off'] + 12, pack('I', 0), 4)) {
            return false;
        }

        return true;
    }
    /* }}} */

    /* {{{ public Boolean optimize() */
    /**
     * 字典优化
     *
     * @access public
     * @return Boolean true or false
     */
    public function optimize()
    {
    }
    /* }}} */

    /* {{{ public Boolean truncate() */
    /**
     * 清空字典
     *
     * @access public
     * @return Boolean true or false
     */
    public function truncate()
    {
        if (!unlink($this->dfile) || !self::create($this->dfile, $this->bucket)) {
            return false;
        }

        $this->init();

        return true;
    }
    /* }}} */

    /* {{{ private static Boolean create() */
    /**
     * 写入头信息
     *
     * @access private static
     * @param  string  $file
     * @param  integer $bucket
     * @return Boolean true or false
     */
    private static function create($file, $bucket = 1)
    {
        $dr = dirname($file);
        if (!is_dir($dr) && !mkdir($dr, 0755, true)) {
            return false;
        }

        $bucket = max(1, (int)$bucket);
        return (bool)file_put_contents($file, pack(
            'a3CI4a12', self::DICT_TAGNAME, self::DICT_VERSION,
            $bucket, self::DICT_HASH_PRI, 32 + ($bucket << 2), 0, ''
        ) . str_repeat(pack('I', 0), $bucket), LOCK_EX);
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
        $os = $this->fget(0, 32);
        if (32 != strlen($os)) {
            return false;
        }

        $os = unpack('a3tag/Cver/Ibucket/Iprime/Ifsize/Ilock/a12reversed', $os);
        if (0 !== strcasecmp(self::DICT_TAGNAME, $os['tag'])) {
            return false;
        }

        $this->fsize = filesize($this->dfile);
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

        $gzip   = 0;
        if (self::$compress && strlen($data) > self::MIN_GZIP_LEN) {
            $data   = gzcompress($data);
            $gzip   = 1;
        }

        return pack('CC', $gzip, $type) . $data;
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
        if (strlen($data) < 2) {
            return false;
        }

        list($gzip, $type) = array_values(unpack(
            'Cgzip/Ctype', substr($data, 0, 2)
        ));
        $data   = substr($data, 2);

        if ($gzip > 0) {
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

    /* {{{ private Mixture fget() */
    /**
     * 读取文件
     *
     * @access private
     * @return string
     */
    private function fget($off, $len)
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

    /* {{{ private Boolean fset() */
    /**
     * 写入文件
     *
     * @access private
     * @return Boolean true or false
     */
    private function fset($off, $data, $len = -1)
    {
        if (false === ($fd = fopen($this->dfile, 'rb+'))) {
            return false;
        }

        $len = ($len < 1) ? strlen($len) : (int)$len;
        fseek($fd, $off, SEEK_SET);
        flock($fd, LOCK_EX);
        $ret = ($len == fwrite($fd, $data, $len)) ? true : false;
        fflush($fd);
        flock($fd, LOCK_UN);
        fclose($fd);

        return $ret;
    }
    /* }}} */

    /* {{{ private Integer slab() */
    /**
     * 获取可利用的空间偏移量
     *
     * @access private
     * @return Integer
     */
    private function slab($len)
    {
        return $this->fsize;
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
            $si += ($si << 5);
            $si ^= ord(substr($key, $i, 1));
            $si &= 0x7fffffff;
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
        $ptr = 32 + ($this->hash($key) << 2);
        $buf = $this->fget($ptr, 4);
        if (4 == strlen($buf)) {
            $off = unpack('Ioff', $buf);
            $off = $off['off'];
        } else {
            $off = $ptr;
        }

        return $this->tree($off, $key, $ptr);
    }
    /* }}} */

    /* {{{ private Mixture tree() */
    /**
     * 二叉树中搜索
     *
     * @access private
     * @return Mixture
     */
    private function tree($off, $key, $ptr = 0)
    {
        if (empty($off)) {
            return array('ptr' => $ptr);
        }

        $len = 20 + self::MAX_KEY_LEN;
        $buf = $this->fget($off, $len);
        if (strlen($buf) < 20) {
            return array('ptr' => $ptr);
        }

        list($loff, $roff, $klen, $vlen, $mlen) = array_values(unpack(
            'Iloff/Iroff/Iklen/Ivlen/Imlen', substr($buf, 0, 20)
        ));
        $idx = substr($buf, 20, $klen);
        $cmp = (strlen($key) == 0) ? 0 : strcmp($key, $idx);
        if ($cmp > 0) {
            return $this->tree($roff, $key, $off + 4);
        }

        if ($cmp < 0) {
            return $this->tree($loff, $key, $off);
        }

        $data   = null;
        if (!empty($vlen)) {
            $data   = substr($buf, 20 + $klen, $vlen);
            if (($ln = $klen + $vlen - self::MAX_KEY_LEN) > 0) {
                $data .= $this->fget($off + $len, $ln);
            }
        }

        return array(
            'ptr'   => $ptr,
            'off'   => $off,
            'loff'  => $loff,
            'roff'  => $roff,
            'klen'  => $klen,
            'vlen'  => $vlen,
            'mlen'  => $mlen,
            'key'   => $idx,
            'data'  => $data,
        );
    }
    /* }}} */

}

