<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Lina Wolf <112@linawolf.de>
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

class tx_timtabCatMenuUserFunc extends tslib_pibase {
	var $cObj = null;

  function makeMenuArray($content,$conf, $startUid='0')    {
  	if(!$this->cObj)
  		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$current = t3lib_div::_GP('tt_news');
		$current = intval($current['cat']);
  	if($startUid==0)
  		$startUid=intval($conf['startUid']);
		$sql = 'SELECT * FROM tt_news_cat WHERE parent_category="'.$startUid.'" '.$this->cObj->enableFields('tt_news_cat');

		$res = mysql(TYPO3_db,  $sql);
		$catMenu = array();
		while($row = mysql_fetch_assoc($res)) {
			$state = 'NO';
			$hasSub = '';
			$subMenu = $this->makeMenuArray($content,$conf, $row['uid']);
			foreach($subMenu AS $value) {
				if($value['ITEM_STATE'] != 'NO' && $value['ITEM_STATE'] != 'IFSUB')
					$state = 'ACT';
			}
			if($current == $row['uid'])
				$state = 'CUR';
			$linkConf = array(
				'parameter' => $conf['catPid'],
				'additionalParams' => '&tx_ttnews[cat]='.$row['uid'],
				'useCacheHash' => '1',
				'returnLast' => 'url',
			);
			$catMenu[] = array( 
				'title' => $row['title'],
         '_OVERRIDE_HREF' => $this->cObj->typolink("",$linkConf),
        'uid' => $conf['catPid'],
        '_ADD_GETVARS' => '&tt_news[cat]='.$row['uid'],
        '_SUB_MENU' => $subMenu,
        'ITEM_STATE' => $state,
			); 
			
		}
		return $catMenu;
  }
}
?>