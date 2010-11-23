<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
// +------------------------------------------------------------------------+
// | 百度服务处理类	    					    							|
// +------------------------------------------------------------------------+
// | Copygight (c) 2010 Aleafs.Com. All Rights Reserved						|
// +------------------------------------------------------------------------+
// | Author: zhangxc <zhangxc83@sohu.com>									|
// +------------------------------------------------------------------------+
//
// $Id: autoload.php 22 2010-04-15 16:28:45Z zhangxc83 $
//

class Aleafs_Sem_Service_Baidu extends Aleafs_Sem_Service
{
	/**
	 * 对外接口，获取一个广告的位置
	 *
	 * @param class $params
	 * @return array
	 */
	public function adrank($params)
	{
		$this->setSoapHeader('soap/baidu');
        if (empty($this->authenticated)) {
            return array("rank" => 0, "cmatch" => 0);
        }
        
        $strWebPage = $this->_uncompress($params->webpage);
        $arrShowUrl = array();
        if (is_array($params->showurl)) {
        	$arrShowUrl = $params->showurl;
        } else {
        	$arrShowUrl = array($params->showurl);
        }
        
        //file_put_contents("webpage.txt", gettype($params->showurl)."\n".count($arrShowUrl)."\t".$arrShowUrl[0]."\n".$strWebPage);
        $arrAdList = $this->_getAdList($strWebPage);
        //file_put_contents("showurl.txt", count($arrAdList[0])."\t".count($arrAdList[1])."\t".count($arrAdList[2])."\n".$params->keyword."\n".$params->showurl. "\n" . $params->showurl[0]. "\n" .$params->showurl[1]);

        return $this->_getAdPos($params->keyword, $arrShowUrl, $arrAdList);
	}
	
	/**
	 * 得到关键词的Q值
	 *
	 * @param array $params
	 * @return array
	 */
	public function keyquality($params)
	{
		$this->setSoapHeader('soap/baidu');
        if (empty($this->authenticated)) {
            return array();
        }     
        //file_put_contents("webpage.txt", gettype($params->keywords)."\n".gettype($params->keywords[0])."\n");
        //二维数组
        if (!is_array($params->keywords))
        {
        	$arrWords = array($params->keywords);
        } else {
        	$arrWords = $params->keywords;
        }
        
        $arrRet = array();
        
        foreach ($arrWords as $arrOne) {
        	$intKeyQ = Aleafs_Sem_Quality::getKeywordQ($arrOne->keywid, $arrOne->q);
        	$arrRet[] = array("keywid" => $arrOne->keywid, 'q' => $intKeyQ);
        }
        
        return $arrRet;
	}
	
	/**
	 * 得到广告位置, multi-showurl
	 *
	 * @param string $keyword
	 * @param array $arrUrl
	 * @param array $arrAdList
	 * @return array
	 */
	private function _getAdPos($keyword, $arrUrl, $arrAdList)
	{
		//cmatch: 1 pp 2 ppim 3 im
		//rank: 第一位为1
		$arrRet = array("rank" => 0, "cmatch" => 0);
		
		foreach ($arrUrl as $strUrl) {
			$ret = $this->_getAdPosOneUrl($keyword, $strUrl, $arrAdList, $arrRet);
			if ($ret >= 0) {
				if ($ret > 0)  {
					$arrRet = array("rank" => 0, "cmatch" => 0);
				}
				break;
			}
		}
		
		return $arrRet;
	}
	
	/**
	 * 得到广告位置，一个showurl
	 *
	 * @param string $keyword
	 * @param string $strUrl
	 * @param array $arrAdList
	 * @param reference array $arrRet
	 * @return int 0 success  1 存在多个匹配  -1 无匹配
	 */
	private function _getAdPosOneUrl($keyword, $strUrl, $arrAdList, &$arrRet)
	{
		//cmatch: 1 pp 2 ppim 3 im
		//rank: 第一位为1
		$arrRet = array("rank" => 0, "cmatch" => 0);
		
		$intMatchNum = 0;
		
		foreach ($arrAdList as $key => $value)
		{
			$intCount = 0;
			foreach ($value as $ad) {
				$intCount ++;
				if (strpos($ad, $strUrl) !== false) 
				{
					$intMatchNum ++;
					$arrRet['cmatch'] = $key + 1;
					$arrRet['rank'] = $intCount;
				}
			}
		}
		
		if ($intMatchNum > 1) {
			return 1;
		} else if ($intMatchNum == 1) {
			return 0;
		} else {
			return -1;
		}
		
	}
	
	/**
	 * 从广告页中分析出广告列表
	 *
	 * @param string $webpage
	 * @return array 0: PP  1: PPIM  2: IM
	 */
	private function _getAdList($webpage)
	{
			$arrRet = array(0 => array(), 1 => array(), 2 => array());
			
			$PPPattern = "/<table id=\"300[0-9]\".*?<\/table>/";
			$PPIMPattern = "/<table id=\"400[0-9]\".*?<\/table>/s";
			$IMPattern = "/<div id=\"bdfs[0-9]\".*?<\/div>/";
			
			$arrMatch = array();
			preg_match_all($PPPattern, $webpage, $arrMatch, PREG_SET_ORDER);
			for ($i = 0; $i < count($arrMatch); $i ++)
			{
				$arrRet[0][] = $arrMatch[$i][0];
			}
			
			$arrMatch = array();
			preg_match_all($PPIMPattern, $webpage, $arrMatch, PREG_SET_ORDER);
			for ($i = 0; $i < count($arrMatch); $i ++)
			{
				$arrRet[1][] = $arrMatch[$i][0];
			}
			
			$arrMatch = array();
			preg_match_all($IMPattern, $webpage, $arrMatch, PREG_SET_ORDER);
			for ($i = 0; $i < count($arrMatch); $i ++)
			{
				$arrRet[2][] = $arrMatch[$i][0];
			}
			
			return $arrRet;
	}
	
	/**
	 * *解压缩、解密字符串
	 *
	 * @param string $webpage
	 * @return string 
	 */
	private function _uncompress($webpage)
	{
			$strRet = base64_decode($webpage);
			//前4个字节记录压缩前长度，后4个字节备用
			$strRet = substr($strRet, 8);
			$strRet = gzinflate($strRet);
			
			return $strRet;
	}
	
}
