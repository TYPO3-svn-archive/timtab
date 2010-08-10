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
require_once($PATH_timtab.'lib/class.tx_timtab_lib.php');

class tx_timtab_latestcomments extends tslib_pibase {

/**
 * Widget 'lastest comments' for the 'TIMTAB' extension.
 *
 * @author	Lina Wolf <2010@lotypo3.de>
 */
 	var $temp_cObj = null;
	var $cObj = null;
	var $widgetType = 'latestComments';
	
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
		$confWidget = $conf['widgets.']['latestComments.'];
		$max = $this->cObj->stdWrap( $confWidget['max'], $confWidget['max.']);
		$showTrackbacks = $this->cObj->stdWrap( $confWidget['showTrackbacks'], $confWidget['showTrackbacks.']);
		
		$trackbacksWhere = '';
		if(!$showTrackbacks) {
			$trackbacksWhere = ' AND tx_timtab_type != "trackback"';
		}
		$where = 'external_prefix="tx_ttnews" '.$this->cObj->enableFields('tx_comments_comments').' AND approved=1 '.$checkPid.$trackbacksWhere;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_comments_comments',
			$where,
			'',
			'uid DESC',
			$max
		);
		$renderLatestCommentsItem = '';
		$count = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$renderLatestCommentsItem .= $this->renderLatestCommentsItem($row, $confWidget);
			$count++;
		}
		
		$this->temp_cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->temp_cObj->data = array();
		$this->temp_cObj->data['renderLatestCommentsItem'] = $renderLatestCommentsItem;
		$this->temp_cObj->data['count'] = $count;
		
		$content = $this->temp_cObj->cObjGetSingle($confWidget['renderLatestCommentsList'], $confWidget['renderLatestCommentsList.']);
		return $content;
	}
	
	
	function renderLatestCommentsItem($row, $confWidget) {
		$content = '';
		$linkAnchor = $this->cObj->stdWrap( $confWidget['linkAnchor'], $confWidget['linkAnchor.']);
		
		$commentLinkAnchor = $this->cObj->wrap($row['uid'],$linkAnchor);
		$this->temp_cObj->data = $row;
		
		$this->temp_cObj->data['tt_news_uid'] = intval($row['external_ref']);
		$ttnews_uid = explode('_',$row['external_ref']);
		$ttnews_uid = $ttnews_uid[count($ttnews_uid)-1];
		$this->temp_cObj->data['renderedLink'] = tx_timtab_lib::getSingleViewLink($this->cObj, $ttnews_uid, $commentLinkAnchor );
		
		$content = $this->temp_cObj->cObjGetSingle($confWidget['renderLatestCommentsItem'], $confWidget['renderLatestCommentsItem.']);
		return $content;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/latestcomments/class.tx_timtab_latestcomments.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/latestcomments/class.tx_timtab_latestcomments.php']);
}
?>