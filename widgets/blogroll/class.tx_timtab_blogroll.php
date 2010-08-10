<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Lina Wolf (2010@lotypo3.de)
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

class tx_timtab_blogroll extends tslib_pibase {

/**
 * Widget 'blogroll' for the 'TIMTAB' extension.
 *
 * @author	Lina Wolf <2010@lotypo3.de>
 * based on code from Ingo Renner
 */
 	var $temp_cObj = null;
	var $cObj = null;
	var $widgetType = 'blogroll';
	
	function init() {
		$this->temp_cObj = t3lib_div::makeInstance('tslib_cObj');
	}
	
	function render($params, $pObj) {
		if($params['widgetType'] != $this->widgetType)
			return $params['content'];
		$this->init();
		$this->cObj =  $params['pObj']->cObj;
		$conf = $params['conf'];
		$checkPid = ' ';
		if($params['pidList']) {
			$checkPid = ' AND pid IN ('.$params['pidList'].') ';
		}
		
		
		$confWidget = $conf['widgets.']['blogroll.'];
		$content = '';
		
		$where = '1=1'.$this->cObj->enableFields('tx_timtab_blogroll').$checkPid;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_timtab_blogroll',
			$where,
			'',
			'sorting'
		);
		
		$count = 0;
		$renderBlogrollItem = '';
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$renderBlogrollItem .= $this->renderBlogrollItem($row,$confWidget);
			$count++;
		}
		
		$this->temp_cObj->data = array();
		$this->temp_cObj->data['renderBlogrollItem'] = $renderBlogrollItem;
		$this->temp_cObj->data['count'] = $count;
		$content = $this->temp_cObj->cObjGetSingle($confWidget['renderBlogrollList'], $confWidget['renderBlogrollList.']);
		return $content;
	}
	
	
 	function renderBlogrollItem($row,$confWidget) {
		$content = '';
		$this->temp_cObj->data = $row;
		$this->temp_cObj->data['foaf'] = $this->buildRelAttr($row);
		$content = $this->temp_cObj->cObjGetSingle($confWidget['renderBlogrollItem'], $confWidget['renderBlogrollItem.']);
		return $content;
	}
	
	
	/**
	 * builds the rel attribute for the anchor
	 *
	 * @author Ingo Renner
	 * @param	array		data row of the current link
	 * @return	string		the rel attribute
	 */
	function buildRelAttr($row) {
		$rel = array();

		if($row['rel_identity'] == 1) {
			return ' rel="me"';
		}

		switch($row['rel_friendship']) {
			case 1:
				$rel[] = 'acquaintance';
				break;
			case 2:
				$rel[] = 'contact';
				break;
			case 3:
				$rel[] = 'friend';
				break;
		}

		if($row['rel_physical'] == 1) {
			$rel[] = 'met';
		}

		//bitmask!
		switch($row['rel_professional']) {
			case 1:
				$rel[] = 'co-worker';
				break;
			case 2:
				$rel[] = 'colleague';
				break;
			case 3: //1 + 2 = 3
				$rel[] = 'co-worker colleague';
				break;
		}

		switch($row['rel_geographical']) {
			case 1:
				$rel[] = 'co-resident';
				break;
			case 2:
				$rel[] = 'neighbor';
				break;
		}

		switch($row['rel_family']) {
			case 1:
				$rel[] = 'child';
				break;
			case 2:
				$rel[] = 'kin';
				break;
			case 3:
				$rel[] = 'parent';
				break;
			case 4:
				$rel[] = 'sibling';
				break;
			case 5:
				$rel[] = 'spouse';
				break;
		}

		//until here we can have a maximum of 6 relationship attributes
		//romantic is a bitmask: 1 2 4 8
		$bitmask = $row['rel_romantic'];
		if(($bitmask - 8) >= 0 ) {
			$rel[9] = 'sweetheart'; //9 is the maximum key of relationship attributes
			$bitmask -= 8;
		}
		if(($bitmask - 4) >= 0 ) {
			$rel[8] = 'date';
			$bitmask -= 4;
		}
		if(($bitmask - 2) >= 0 ) {
			$rel[7] = 'crush';
			$bitmask -= 2;
		}
		if(($bitmask - 1) >= 0 ) {
			$rel[6] = 'muse';
			$bitmask -= 1;
		}

		ksort($rel);

		//put everything together
		if(is_array($rel) && count($rel) > 0) {
			$relAttr = ' rel="'.implode(' ', $rel).'"';
		} else {
			$relAttr = '';
		}

		return $relAttr;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/blogroll/class.tx_timtab_blogroll.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/blogroll/class.tx_timtab_blogroll.php']);
}
?>