<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | Q值优化类			    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
// 注意时区问题，dispatcher中设置 date_default_timezone_set('Asia/Shanghai');

class Aleafs_Sem_Quality
{

    /* {{{静态常量 */

    private static  $qrange = array(1 => array("min" => 30, "max" => 49), 
    									 2 => array("min" => 50, "max" => 69),
    									 3 => array("min" => 70, "max" => 93));
	
    const TABLE_PREFIX = "";
    
    /* }}} */

    /* {{{ 静态变量 */

    private static $loader;

    /* }}} */

    
    /**
     * 得到随机生成的Q值
     *
     * @param int $intq
     * @return int
     */
    public static function getRandQ($intq)
    {
    	if ($intq < 1 || $intq > 3)
    	{
    		$intq = 1;
    	}
    	
    	$intMin = self::$qrange[$intq]["min"];
    	$intMax = self::$qrange[$intq]["max"];
    	
    	return rand($intMin, $intMax);
    }
    
	/**
	 * 得到关键词的Q值
	 *
	 * @param int $keywid
	 * @param int $intq
	 * @return  int
	 */
    public static function getKeywordQ($keywid, $intq)
    {
        self::initDb();

        self::$loader->table(sprintf('%sbaidu_word_q', self::TABLE_PREFIX))
            ->where('keywid', $keywid);

        $arrKeyInfo = self::$loader->select('qvalue', 'modtime', 'qold')->getRow();
        
        //没有时插入
        if (!is_array($arrKeyInfo) || empty($arrKeyInfo)) {
        	$intQvalue = self::getRandQ($intq);
        	self::insertKeywordQ($keywid, $intQvalue, $intq);	
        } else {
        	$intQvalue = intval($arrKeyInfo['qvalue']);
        	$intQold = intval($arrKeyInfo['qold']);
        	$strModTime = trim($arrKeyInfo['modtime']);
        	$strModTime = substr($strModTime, 0, 10);
        	//有，但过期时更新
        	if ($intQold != $intq || $strModTime != date("Y-m-d"))
        	{
        		$intQvalue = self::getRandQ($intq);
        		self::updateKeywordQ($keywid, $intQvalue, $intq);	
        	}
        }
        
        return $intQvalue;
    }

    /**
     * 插入关键词Q值
     *
     * @param int $keywid
     * @param int $intQvalue
     * @return ints
     */
    public static function insertKeywordQ($keywid, $intQvalue, $intq)
    {
    	$arrParam = array("keywid" => $keywid, "qvalue" => $intQvalue, "qold" => $intq);
    	$arrParam['modtime'] = date('Y-m-d H:i:s');
    	
    	self::initDb();
        return self::$loader->table(sprintf('%sbaidu_word_q', self::TABLE_PREFIX))
        ->insert($arrParam)->affectedRows();
    }

    /**
     * 更新关键词Q值
     *
     * @param int $keywid
     * @param int $intQvalue
     * @return int
     */
	public static function updateKeywordQ($keywid, $intQvalue, $intq)
    {
    	$arrParam = array("qvalue" => $intQvalue, "qold" => $intq);
    	$arrParam['modtime'] = date('Y-m-d H:i:s');
    	
    	self::initDb();
        return self::$loader->table(sprintf('%sbaidu_word_q', self::TABLE_PREFIX))
        ->where('keywid', $keywid)->update($arrParam)->affectedRows();
    }
    
    
    /* {{{ public static Integer deleteKeywordQ() */
    public static function deleteKeywordQ($keywid)
    {
        self::initDb();

        return self::$loader->table(sprintf('%sbaidu_word_q', self::TABLE_PREFIX))
            ->where('keywid', $keywid)->delete()->affectedRows();
    }
    /* }}} */
    
    /* {{{ private static Mixture column() */
    /**
     * 获取表结构
     *
     * @access private static
     * @return Mixture
     */
    private static function column($table)
    {
        $table  = trim($table);
        if (empty(self::$column[$table])) {
            self::initDb();
            $column = self::$loader->getAll(self::$loader->query(sprintf('DESC %s', $table)));
            foreach ($column AS $row) {
                self::$column[$table][$row['Field']] = $row['Type'];
            }
        }

        return self::$column[$table];
    }
    /* }}} */

    /* {{{ private static void initDb() */
    /**
     * 初始化DB对象
     *
     * @access private static
     * @return void
     */
    private static function initDb()
    {
        if (empty(self::$loader)) {
            self::$loader   = new Aleafs_Lib_Db_Mysql('mysql');
        }
        self::$loader->clear();
    }
    /* }}} */

}