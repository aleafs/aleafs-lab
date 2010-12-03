<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +--------------------------------------------------------------------+
// | Session存储MySQL类													|
// +--------------------------------------------------------------------+
// | Copyright (c) 2010 Taobao.com. All Rights Reserved					|
// +--------------------------------------------------------------------+
// | Author: pengchun <pengchun@taobao.com>								|
// +--------------------------------------------------------------------+
//
// $Id: session.php 2010-04-19 13:54:32 pengchun Exp $

class Aleafs_Lib_Session_Mysql
{

	/* {{{ 静态变量 */

	private static $attrMap	= array(
		Aleafs_Lib_Session::TS	=> 'actime',
		Aleafs_Lib_Session::IP	=> 'ipaddr',
	);

	/* }}} */

	/* {{{ 成员变量 */

	private $mysql	= null;

    private $table	= null;

    private $readed = false;

    private $exists = false;

	/* }}} */

	/* {{{ public void __construct() */
	/**
	 * 构造函数
	 *
     * @access public
     * @param  String $names : mysql/table
	 * @return void
	 */
	public function __construct($names)
	{
		list($mysql, $this->table) = explode('/', $names, 2);
		$this->mysql	= new Aleafs_Lib_Db_Mysql($mysql);
	}
	/* }}} */

	/* {{{ public Mixture get() */
	/**
	 * 读取数据
	 *
	 * @access public
	 * @return Mixture
	 */
	public function get($key, &$attr)
    {
        $attr = array();
		$data = $this->mysql->clear()->table($this->table)
			->where('sesskey', $key)
            ->select('sessval', 'actime', 'ipaddr')->getRow();
        $this->readed  = true;
        if (empty($data)) {
            $this->exists  = false;
            return null;
        }

        $this->exists  = true;
        foreach (self::$attrMap AS $key => $val) {
            $attr[$key] = isset($data[$val]) ? $data[$val] : null;
        }

        return json_decode($data['sessval'], true);
	}
	/* }}} */

	/* {{{ public Boolean set() */
	/**
	 * 写入数据
	 *
	 * @access public
	 * @return Boolean true or false
	 */
	public function set($key, $data, $attr = null)
    {
        if (empty($this->readed)) {
            $this->get($key, $lala);
        }

        $column = array(
            'sessval'   => json_encode($data),
        );
        foreach ((array)$attr AS $k => $v) {
            if (empty(self::$attrMap[$k])) {
                continue;
            }

            $column[self::$attrMap[$k]] = $v;
        }

        $this->mysql->clear()->table($this->table);
        if ($this->exists) {
            $this->mysql->where('sesskey', $key)->update($column);
        } else {
            $column['sesskey'] = $key;
            $this->mysql->insert($column);
        }

        return $this->mysql->affectedRows();
	}
	/* }}} */

	/* {{{ public Boolean delete() */
	/**
	 * 删除数据
	 *
	 * @access public
	 * @return void
	 */
	public function delete($key)
	{
		return $this->mysql->clear()->table($this->table)
			->where('sesskey', $key)
			->delete()->affectedRows();
	}
	/* }}} */

	/* {{{ public Boolean gc() */
	/**
	 * 垃圾回收
	 *
	 * @access public
	 * @return Boolean true or false
	 */
	public function gc($tm)
	{
		return $this->mysql->clear()->table($this->table)
			->where('actime', (int)$tm, Aleafs_Lib_Database::LT, false)
			->delete()->affectedRows();
	}
	/* }}} */

}

