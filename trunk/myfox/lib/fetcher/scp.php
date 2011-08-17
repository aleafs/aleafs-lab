<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | SCP获取文件							    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2003 - 2010 Taobao.com. All Rights Reserved				|
// +------------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>									|
// +------------------------------------------------------------------------+

namespace Myfox\Lib\Fetcher;

class Scp
{

    /* {{{ 成员变量 */

    private $error;

    /* }}} */

	/* {{{ public Boolean getfile() */
	/**
	 * 获取远程文件
	 *
	 * @access public
	 * @return Booelean true or false
	 */
	public function getfile($url, $file)
    {
        $url['user'] = empty($url['user']) ? get_current_user() : $url['user'];
        if (!$this->isChange($url, $file)) {
            return true;
        }

		return self::ssh(sprintf('scp %s@%s:%s %s',
			escapeshellcmd($url['user']),
			escapeshellcmd($url['host']),
            escapeshellcmd($url['path']),
            escapeshellcmd($file)
        ), $this->error);
	}
	/* }}} */

    /* {{{ public Mixture __get() */
    /**
     * 魔术方法
     *
     * @access public
     * @return Mixture
     */
    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }
    /* }}} */

    /* {{{ public static Mixture parsestat() */
    /**
     * 解析ls -l --full-time的结果
     *
     * @access public static
     * @return Mixture
     */
    public static function parsestat($stat)
    {
        $stat   = explode(' ', trim($stat));
        return array(
            'size'  => $stat[4],
            'mtime' => strtotime(sprintf('%s %s', $stat[5], substr($stat[6], 0, 8), $stat[7])),
        );
    }
    /* }}} */

    /* {{{ private Boolean isChange() */
    /**
     * 文件是否变化
     *
     * @access private
     * @return Boolean true or false
     */
    private function isChange($url, $file)
    {
        if (!is_file($file)) {
            return true;
        }

        $rt = self::ssh(sprintf(
            'ssh %s@%s "ls -l --full-time \"%s\""',
			escapeshellcmd($url['user']),
			escapeshellcmd($url['host']),
			escapeshellcmd($url['path'])
        ), $stat);
        if (!empty($rt)) {
            return true;
        }

        $stat   = self::parsestat(trim($stat));
        if ($stat['size'] != filesize($file) || $stat['mtime'] >= filemtime($file)) {
            return true;
        }

        return false;
    }
    /* }}} */

    /* {{{ private Boolean ssh() */
    /**
     * 带超时控制的ssh命令
     *
     * @access private
     * @return Boolean true or false
     */
    private static function ssh($script, &$output)
    {
        $return = exec(sprintf('%s 2>&1', $script), $output, $rt);
        $output = !empty($return) ? $return : $output;

        return empty($rt) ? true : false;
    }
    /* }}} */

}

