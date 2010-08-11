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

/**
 * Widget 'lastest comments' for the 'TIMTAB' extension.
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author	Lina Wolf <2010@lotypo3.de>
 * @author	Timo Webler <timo.webler@dkd.de>
 * @version $Id:$
 */

$pathTimtab = t3lib_extMgm::extPath('timtab');
require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once($pathTimtab . 'lib/class.tx_timtab_lib.php');

/**
 * Widget 'lastest comments' for the 'TIMTAB' extension.
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author	Lina Wolf <2010@lotypo3.de>
 * @author	Timo Webler <timo.webler@dkd.de>
 * @version $Id:$
 */
class tx_timtab_Latestcomments extends tslib_pibase {

	/**
	 * content object
	 *
	 * @var tslib_cObj
	 */
	protected $tempCObj = NULL;

	/**
	 * content object
	 *
	 * @var tslib_cObj
	 */
	public $cObj = NULL;

	/**
	 * widget type
	 *
	 * @var string
	 */
	protected $widgetType = 'latestComments';

	/**
	 * initializes the widget
	 *
	 * @return void
	 */
	protected function init() {
		$this->tempCObj = t3lib_div::makeInstance('tslib_cObj');
	}

	/**
	 * render the widget
	 *
	 * @param array $params parameter from callUserFunc
	 * @param tx_timtab_pi1 $pObj plugin object
	 * @return string
	 */
	public function render($params, $pObj) {
		if ($params['widgetType'] != $this->widgetType) {
			return $params['content'];
		}
		$this->init();
		$this->cObj =  $params['pObj']->cObj;
		$conf = $params['conf'];
		$checkPid = ' ';
		if ($params['pidList']) {
			$checkPid = ' AND pid IN (' . $params['pidList'] . ') ';
		}
		$confWidget = $conf['widgets.']['latestComments.'];
		$max = $this->cObj->stdWrap($confWidget['max'], $confWidget['max.']);
		$showTrackbacks = $this->cObj->stdWrap($confWidget['showTrackbacks'], $confWidget['showTrackbacks.']);

		$trackbacksWhere = '';
		if (!$showTrackbacks) {
			$trackbacksWhere = ' AND tx_timtab_type != "trackback"';
		}
		$where = 'external_prefix="tx_ttnews" ' . $this->cObj->enableFields('tx_comments_comments') .
			' AND approved=1 ' . $checkPid . $trackbacksWhere;
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

		$this->tempCObj = t3lib_div::makeInstance('tslib_cObj');
		$this->tempCObj->data = array();
		$this->tempCObj->data['renderLatestCommentsItem'] = $renderLatestCommentsItem;
		$this->tempCObj->data['count'] = $count;

		$content = $this->tempCObj->cObjGetSingle($confWidget['renderLatestCommentsList'], $confWidget['renderLatestCommentsList.']);
		return $content;
	}

	/**
	 * render on comments items
	 *
	 * @param array $row comments data
	 * @param array $confWidget widget configuration
	 * @return string
	 */
	protected function renderLatestCommentsItem($row, $confWidget) {
		$content = '';
		$linkAnchor = $this->cObj->stdWrap($confWidget['linkAnchor'], $confWidget['linkAnchor.']);

		$commentLinkAnchor = $this->cObj->wrap($row['uid'], $linkAnchor);
		$this->tempCObj->data = $row;

		$this->tempCObj->data['tt_news_uid'] = intval($row['external_ref']);
		$ttnewsUid = explode('_', $row['external_ref']);
		$ttnewsUid = $ttnewsUid[count($ttnewsUid) - 1];
		$this->tempCObj->data['renderedLink'] = tx_timtab_Lib::getSingleViewLink($this->cObj, $ttnewsUid, $commentLinkAnchor);

		$content = $this->tempCObj->cObjGetSingle($confWidget['renderLatestCommentsItem'], $confWidget['renderLatestCommentsItem.']);
		return $content;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/latestcomments/class.tx_timtab_latestcomments.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/latestcomments/class.tx_timtab_latestcomments.php']);
}
?>