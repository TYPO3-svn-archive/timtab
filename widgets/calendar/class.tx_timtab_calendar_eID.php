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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
* class.tx_timtab_calendar_eID.php
*
* Ajax for timtab calendar
*
* $Id: class.tx_comments_eID.php 15529 2009-01-07 10:04:22Z dmitry $
*
* inspired by code of Dmitry Dulepov <dmitry@typo3.org>
* @author Lina Wolf <2010@lotypo3.de>
*/


require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
require_once(PATH_site . 't3lib/class.t3lib_tcemain.php');

/**
 * Ajax for timtab calendar
 *
 * @author Lina Wolf <2010@lotypo3.de>
 * @package TYPO3
 * @subpackage tx_timtab
 */
class tx_timtab_calendar_eID {
	var $uid;
	var $command;

	function init() {
		$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
		$GLOBALS['LANG']->init('default');
	//	$GLOBALS['LANG']->includeLLFile('EXT:comments/locallang_eID.xml');

		tslib_eidtools::connectDB();
		tslib_eidtools::initFeUser();
 
		// initialize TSFE, code from http://www.zoe.vc/2008/typoscript-auslesen/
		require_once(PATH_tslib.'class.tslib_fe.php');
		require_once(PATH_t3lib.'class.t3lib_page.php');
		$temp_TSFEclassName = t3lib_div::makeInstance('tslib_fe');
		$GLOBALS['TSFE'] = new $temp_TSFEclassName($TYPO3_CONF_VARS, $pid, 0, true);
		$GLOBALS['TSFE']->connectToDB();
		$GLOBALS['TSFE']->initFEuser();
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->getCompressedTCarray();
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->getConfigArray();

		// Sanity check
		/*
		$this->uid = t3lib_div::_GET('uid');
		if (!t3lib_div::testInt($this->uid)) {
			echo $GLOBALS['LANG']->getLL('bad_uid_value');
			exit;
		}
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t', 'tx_comments_comments', 'uid=' . $this->uid);
		if ($rows[0]['t'] == 0) {
			echo $GLOBALS['LANG']->getLL('comment_does_not_exist');
			exit;
		}

		$check = t3lib_div::_GET('chk');
		if (md5($this->uid . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']) != $check) {
			echo $GLOBALS['LANG']->getLL('wrong_check_value');
			exit;
		}
		$this->command = t3lib_div::_GET('cmd');
		if (!t3lib_div::inList('approve,delete,kill', $this->command)) {
			echo $GLOBALS['LANG']->getLL('wrong_cmd');
			exit;
		}
		*/
	}

	/**
	 * Main processing function of eID script
	 *
	 * Echos the whole calender generated as HTML
	 *
	 * @return	void
	 */
	function main() {
		$calendar = t3lib_div::makeInstance('tx_timtab_calendar');
		$params = array();
		$params['pObj'] = 'ajax';
		$params['conf'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab_pi1.'];
		$params['pidList'] = '5';
		$params['pObj'] = 'ajax';
		$params['ajaxStartDate'] = t3lib_div::_GP('startdate');
		$params['widgetType'] = 'calendar';
		echo $calendar->render($params);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/calendar/class.tx_timtab_calendar_eID.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/calendar/class.tx_timtab_calendar_eID.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_timtab_calendar_eID');
$SOBE->init();
$SOBE->main();
?>