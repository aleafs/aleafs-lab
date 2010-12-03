<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 关键词推荐类			    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
/**
user_krtask:  
status   任务状态 0  运行中     5已完成 
*/

class Aleafs_Sem_Kr
{
	 /* {{{静态常量 */
	
    const TABLE_PREFIX = "";
    
    const KrFilePath = "../resource/kr/";
    
    /* {{{ 静态变量 */

    private static $loader;
   
	public static function addKrTask($arrInfo)
	{
    	$arrInfo['addtime'] = date('Y-m-d H:i:s');
    	
    	self::initDb();
        return self::$loader->table(sprintf('%suser_krtask', self::TABLE_PREFIX))
        ->insert($arrInfo)->affectedRows();
	}
	
	/**
	 * 得到一个用户的所有待优化关键词
	 *
	 * @param unknown_type $strUserName
	 * @return unknown
	 */
	public static function getKrTaskByUsername($strUserName)
	{
    	self::initDb();
        return self::$loader->table(sprintf('%suser_krtask', self::TABLE_PREFIX))
        ->where('username', $strUserName)->select("*")->getAll();
	}
	
	/**
	 * 更新关键词信息
	 *
	 * @param unknown_type $arrInfo
	 * @return unknown
	 */
	public  static function updateKrTask($arrInfo)
	{
    	$intKeywid = intval($arrInfo['autokid']);
    	$strUserName = trim($arrInfo['username']);
    	unset($arrInfo['autokid']);
    	unset($arrInfo['username']);
    	
    	self::initDb();
        return self::$loader->table(sprintf('%suser_krtask', self::TABLE_PREFIX))
        ->where('autokid', $intKeywid)->where('username', $strUserName)->update($arrInfo)->affectedRows();
	}
	
	/**
	 * 删除待优化关键词
	 *
	 * @param unknown_type $intKeywid
	 * @return unknown
	 */
	public  static function deleteKrTask($intKeywid, $strUserName)
	{  	
    	self::initDb();
        return self::$loader->table(sprintf('%suser_krtask', self::TABLE_PREFIX))
        ->where('autokid', $intKeywid)->where('username', $strUserName)->delete()->affectedRows();
	}

	
	public static function getKrRes($intTaskId)
	{
		$arrRet = array();
		$strFileName = self::KrFilePath. $intTaskId. ".txt";
		if (file_exists($strFileName))
		{
			$fp = fopen($strFileName, "r");
			if (is_resource($fp)) 
			{
				while (!feof($fp))
				{
					$strLine = trim(fgets($fp, 2048));
					if (empty($strLine)) {
						continue;
					}
					
					$arrLine = explode("\t", $strLine);
					if (count($arrLine) != 2) 
					{
						continue;
					}
					$arrTmp = array();
					$arrTmp['keyword'] = $arrLine[0];
					$arrTmp['pv'] = intval($arrLine[1]);
					
					$arrRet[] = $arrTmp;
				}
			}
		}
		
		return $arrRet;
	}
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
?>