<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Ingo Renner (typo3@ingo-renner.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * class.tx_timtab_be.php
 *
 * Class which implements methods to connect to hooks in TCEmain
 *
 * $Id$
 *
 * @author Ingo Renner <typo3@ingo-renner.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   47: class tx_timtab_be
 *   62:     function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj)
 *   79:     function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, $pObj)
 *   93:     function processDatamap_postProcessFieldArray($status, $table, $id, $fieldArray, $pObj)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
$PATH_timtab = t3lib_extMgm::extPath('timtab');
define('PATH_tslib', PATH_site.'tslib/');
require_once($PATH_timtab.'class.tx_timtab_trackback.php');
#require_once($PATH_timtab.'class.tx_timtab_pingback.php');
require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_t3lib.'class.t3lib_timetrack.php');
require_once(PATH_t3lib.'class.t3lib_userauth.php');
require_once(PATH_tslib.'class.tslib_feuserauth.php');
require_once(PATH_tslib.'class.tslib_fe.php');
require_once(PATH_tslib.'class.tslib_content.php');

$TT = new t3lib_timeTrack;
$TT->start();

class tx_timtab_be {
	var $prefixId 		= 'tx_timtab_be';		// Same as class name
	var $scriptRelPath 	= 'class.tx_timtab_be.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'timtab';	// The extension key.
	
	var $conf;
	var $tb; //trackback object
	var $pb; //pingback object
	var $cObj;
	var $pid;
	var $tt_news; //current tt_news record

	/**
	 * initializes the configuration for the extension as we need the TS setup
	 * like blog title and timouts for trackback in the BE, too
	 *
	 * @return	void
	 */
	function init($tt_news_uid) {
		global $TSFE;
		
		$this->pid = intval(t3lib_div::_GP('popViewId'));
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');;		
		
		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$TSFE = new $temp_TSFEclassName(
			$GLOBALS['TYPO3_CONF_VARS'],
			$this->pid,
			'',
			0,
			'',
			'',
			'',
			''
		);
		$TSFE->forceTemplateParsing = 1;
		$TSFE->showHiddenPage = false;	
		$TSFE->initFEuser();
		$TSFE->fetch_the_id();
		$TSFE->initTemplate();
		$TSFE->getConfigArray();
	
		$this->conf = array_merge(
			$TSFE->tmpl->setup['plugin.']['tx_timtab.'], 
			$TSFE->tmpl->setup['plugin.']['tx_timtab_pi2.']
		);
		
		
		//get the current tt_news record
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
 			'*',
 			'tt_news',
 			'uid = '.$tt_news_uid
 		);
 		$this->tt_news = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
 		
#debug($this->tt_news, 'tt_news');
#debug($this->conf, 'this->conf');
	}

	/**
	 * post processing of tt_news entries
	 *
	 * @param	string		not relevant for us
	 * @param	string		telling us which table the record belongs to, we will process tt_news records only
	 * @param	integer		record uid
	 * @param	array		database record
	 * @param	object		the parentobject (TCEmain)
	 * @return	void
	 */
	function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj) {
		//only do something when we get a tt_news record and the bodytext is changed
		if($table == 'tt_news' && $fieldArray['bodytext']) {
			if(substr($id, 0, 3) == 'NEW') { //new record
				$id = $pObj->substNEWwithIDs[$id];	
			}
			$this->init($id);
						
			//processing trackback
			$this->tb = t3lib_div::makeInstance('tx_timtab_trackback');
			$this->tb->init($this);
			
			if($tbURLs = $this->tb->tbAutoDiscovery($this->tt_news['bodytext'])) {
				//found some trackback URLs
				$source = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->buildSourceURL();
				foreach($tbURLs as $k => $target) {
					// Attempt to ping each trackback URL
					if($this->tb->ping($target, $source, $this->tt_news['title'], $this->getExcerpt())) {
						debug(array($target => 'success'));
					} else {
						debug(array($target => 'failed'));
					}	
				}				
			}
			//end processing trackbacks
		}
	}
	
	/**
	 * builds the source URL for thetrackback - the URL where the original author
	 * can find our post
	 * 
	 * @param integer the tt_news uid we are building the URL for
	 * @return string
	 */
	 function buildSourceURL() {
	 	$urlParameters = array(
 			'tx_ttnews[year]'    => date('Y', $this->tt_news['datetime']),
 			'tx_ttnews[month]'   => date('m', $this->tt_news['datetime']),
 			'tx_ttnews[day]'     => date('d', $this->tt_news['datetime']),
 			'tx_ttnews[tt_news]' => $this->tt_news['uid']
 		);
 		
 		return $this->cObj->getTypoLink_URL($this->conf['blogPid'], $urlParameters);
	 }
	 
	 /**
	  * creates a short excerpt of our post for sending it as trackback excerpt
	  * 
	  * @return string an excerpt of the current post
	  */
	 function getExcerpt() {
	 	$excerpt = '';
	 	$max_length = 255; //is not limited by spec but we do
	 	
	 	if(!empty($this->tt_news['short'])) {
	 		$excerpt = $this->tt_news['short'];
	 	} else {
	 		$excerpt = $this->tt_news['bodytext'];
	 	}
	 	
	 	if(strlen($excerpt) > $max_length) {
	 		$excerpt = substr($excerpt, 0, $max_length - 3).'...';
	 	}
	 	
	 	return $excerpt;
	 }

	/**
	 * just here for completenes otherwise we would produces calls to undfined functions in TCEmain
	 *
	 * @param	array		$incomingFieldArray: ...
	 * @param	string		$table: ...
	 * @param	integer		$id: ...
	 * @param	object		$pObj: ...
	 * @return	void
	 */
	function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, $pObj) {
		//do nothing
	}

	/**
	 * just here for completenes otherwise we would produces calls to undfined functions in TCEmain
	 *
	 * @param	string		$status: ...
	 * @param	string		$table: ...
	 * @param	integer		$id: ...
	 * @param	array		$fieldArray: ...
	 * @param	object		$pObj: ...
	 * @return	void
	 */
	function processDatamap_postProcessFieldArray($status, $table, $id, $fieldArray, $pObj) {
		//do nothing
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_be.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_be.php']);
}

?>
