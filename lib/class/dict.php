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
    }
    /* }}} */

    public function set($key, $value)
    {
    }

    public function get($key)
    {
    }

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

}

