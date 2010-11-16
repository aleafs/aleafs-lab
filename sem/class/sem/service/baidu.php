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
        
        //file_put_contents("webpage.txt", gettype($params->showurl)."\n".$arrShowUrl[0]."\n".$strWebPage);
        $arrAdList = $this->_getAdList($strWebPage);
        //file_put_contents("showurl.txt", count($arrAdList[0])."\n".$params->keyword."\n".$params->showurl. "\n" . $params->showurl[0]. "\n" .$params->showurl[1]);

        return $this->_getAdPos($params->keyword, $arrShowUrl, $arrAdList);
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
			
			$PPPattern = "/<table id=\"300[0-9]\"  class=\"ec_pp_f\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">[<tbody>]?<tr><td class=\"f EC_PP\">.*?推广<\/a><\/font><\/td><\/tr><\/table><br>/";
			$PPIMPattern = "/<td style=\"line-height:20px;height:20px;overflow:hidden;width:100%;white-space:nowrap;\" id=\"taw[0-9]\" class=\"f16 EC_PP\".*?<\/font><\/a>/";
			$IMPattern = "/<div id=\"bdfs[0-9]\" class=\"EC_PP\" style=\"word-break:break-all;cursor:hand;width:270px;\">.*?<\/font><\/a><\/div><br>/";
			
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

