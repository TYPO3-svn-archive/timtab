<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Lina Wolf <2010@lotypo3.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_timtabCatMenuUserFunc  extends tslib_pibase {
	var $cObj = null;

  function makeMenuArray($content,$conf, $startUid='0')    {
  	if(!$this->cObj)
  		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->newsEnableFields = $this->cObj->enableFields('tt_news');
		$this->catEnableFields  = $this->cObj->enableFields('tt_news_cat');
		if(!$startUid && $conf['$startUid'])
			$startUid = $conf['$startUid'];
		$currentCatId = t3lib_div::_GP('tt_news');
		$currentCatId = intval($currentCatId['cat']);
  	if($startUid==0)
  		$startUid=intval($conf['startUid']);
  	$filterBySourcePid = '';
  	if($conf['sourcePid'])
  		$filterBySourcePid = ' AND tt_news_cat.pid = "'.intval($conf['sourcePid']).'"';
  	$sortByField = '';
  	if($conf['sortBy'])
  		$sortByField = ' ORDER BY '.$conf['sortBy'];
		$sql = 'SELECT tt_news_cat.*, count(tt_news.uid) AS news_count FROM tt_news_cat 
			LEFT JOIN tt_news_cat_mm ON (tt_news_cat.uid = tt_news_cat_mm.uid_foreign )
			LEFT JOIN tt_news ON (tt_news.uid = tt_news_cat_mm.uid_local '.$this->cObj->enableFields('tt_news').') 
			WHERE parent_category="'.$startUid.'" '.$this->cObj->enableFields('tt_news_cat').$filterBySourcePid.
			' GROUP BY tt_news_cat.uid '.$sortByField;
		#t3lib_div::debug($sql);
		$res = mysql(TYPO3_db,  $sql);
		$catMenu = array();
		while($row = mysql_fetch_assoc($res)) {
			$state = 'NO';
			$hasSub = '';
			$subMenu = array();
			if($conf['recursive'])
				$subMenu = $this->makeMenuArray($content,$conf, $row['uid']);
			$countRec = $row['news_count'];
			foreach($subMenu AS $value) {
				if($value['ITEM_STATE'] != 'NO' && $value['ITEM_STATE'] != 'IFSUB')
					$state = 'ACT';
				$countRec += $value['news_count'];
			}
			if($currentCatId == $row['uid'])
				$state = 'CUR';
			$linkConf = array(
				'parameter' => $conf['catPid'],
				'additionalParams' => '&tx_ttnews[cat]='.$row['uid'],
				'useCacheHash' => '1',
				'returnLast' => 'url',
			);
			$catItem = $row;
			$catItem['news_count_rec'] = $countRec;
			$catItem['_OVERRIDE_HREF'] =  $this->cObj->typolink("", $linkConf);
			$catItem['_ADD_GETVARS'] = '&tt_news[cat]='.$row['uid'];
			$catItem['_SUB_MENU'] =  $subMenu;
			$catItem['ITEM_STATE'] =  $state;
			if($conf['showEmpty'] || $countRec > 0){
				$catMenu[] = $catItem;
			}
			
		}
		return $catMenu;
  }
}
?>