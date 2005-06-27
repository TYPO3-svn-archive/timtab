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
	var $cObj;
	var $pid;
	var $status;

	/**
	 * initializes the configuration for the extension as we need the TS setup
	 * like blog title and timouts for trackback in the BE, too
	 *
	 * @return	void
	 */
	function init() {
		global $TSFE;
		
		$this->pid = intval(t3lib_div::_GP('popViewId'));
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');		
		
		//we need a nearly whole TSFE for getting plugin setup and creation of correct source URLs
		if(!is_object($GLOBALS['TSFE'])) {
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
		}
	
		$this->conf = array_merge(
			$TSFE->tmpl->setup['plugin.']['tx_timtab.'], 
			$TSFE->tmpl->setup['plugin.']['tx_timtab_pi2.']
		);
		
		$this->status == 'new' ? $this->status = 'new' : $this->status = 'update'; 
	}
	
	/**
	 * pre processing of tt_news entries, detecting pings
	 *
	 * @param	string		$status: ...
	 * @param	string		$table: ...
	 * @param	integer		$id: ...
	 * @param	array		$fieldArray: ...
	 * @param	object		$pObj: ...
	 * @return	void
	 */
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $pObj) {
		//only do something when we get a tt_news record and the bodytext is changed
		if($table == 'tt_news' && $fieldArray['bodytext']) {
			if(substr($id, 0, 3) == 'NEW') { //new record
				$id = 0;
				$this->status = 'new';
			}
			$this->init();
			
			//initialize processing of trackbacks
			$tbObj = t3lib_div::makeInstance('tx_timtab_trackback');
			$tbObj->init($this, $fieldArray);
			
			if($foundURLs = $tbObj->tbAutoDiscovery($fieldArray['bodytext'])) {
				
				$newTBs = '';
				if($this->status == 'update') {
					//update a post, find new trackbacks
					$tbField = '';
					if(isset($fieldArray['tx_timtab_trackbacks'])) {
						$tbField = $fieldArray['tx_timtab_trackbacks'];
					} else {
						$tt_news = $this->getCurrentPost($id);
						$tbField = $tt_news['tx_timtab_trackbacks'];
					}					
					$oldTBarray = t3lib_div::trimExplode("\n", $tbField);
					
					$temp = array();
					foreach($oldTBarray as $TB) {
						$parts = explode('|', $TB);
						$temp[] = (string) trim($parts[0]);	
					}
					//extract new TBs
					$newTBarray = array_diff($foundURLs, $temp);					

					unset($TB);
					reset($oldTBarray);
					$oldTBs = '';
					foreach($oldTBarray as $TB) {
						$oldTBs .= $TB.chr(10);
					}

					foreach($newTBarray as $url) {
						$newTBs .= $url.'|0|new'.chr(10);
					}
					$newTBs = trim($oldTBs.$newTBs);

				} elseif($this->status == 'new') {
					//creating a new post			
					foreach($foundURLs as $url) {
						$newTBs .= $url.'|0|new'.chr(10);
					}
					$newTBs = trim($newTBs);
				}

				$fieldArray['tx_timtab_trackbacks'] = $newTBs;
			}
		}
	}
	
	/**
	 * post processing of tt_news entries, sending pings
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
				$this->status = 'new';
			}
			$this->init();
			$tt_news = $this->getCurrentPost($id);			
			
			$tbObj = t3lib_div::makeInstance('tx_timtab_trackback');
			$tbObj->init($this, $tt_news);
			$TBstatus = $this->getTrackbackStatus($tt_news['tx_timtab_trackbacks']);
			
			//processing trackbacks
			if(is_array($TBstatus)) {
				foreach($TBstatus as $k => $TB) {
					// Attempt to ping each trackback URL
					if($TB['status'] == 0) {
						$result = $tbObj->ping($TB['url']);
						if($result[0]) { 
							//success
							$TBstatus[$k]['status'] = 1;
							unset($TBstatus[$k]['reason']);
						} else {
							//failed
							$TBstatus[$k]['reason'] = $result[1];
						}
					}	
				}				
			}
			//end processing trackbacks
			
			//update trackback status in tt_news record
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tt_news',
				'uid = '.$tt_news['uid'],
				array('tx_timtab_trackbacks' => $this->setTrackbackStatus($TBstatus))
			);
		}
	}
	
	/**
	 * gets the current tt_news record we are working on
	 * 
	 * @param	integer	the tt_news uid of the record we want to get
	 * @return	array
	 */
	function getCurrentPost($tt_news_uid) {
		//get the current tt_news record
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
 			'*',
 			'tt_news',
 			'uid = '.$tt_news_uid
 		);
 		return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}
	
	/**
	 * builds an array containing url, status and reason if status is failed
	 * 
	 * @param	string	list of Trackbacks coming from the DB
	 * @return	array	checked and transformed array of trackback URLs enriched with meta information
	 */
	function getTrackbackStatus($TBlist) {
		$tbField = explode("\n", $TBlist);

		$trackbacks = array();
		foreach($tbField as $line) {
			$properties = explode('|', $line);
			
			if($properties[1] == 1) {
				$reason = ''; //ping sent
			} elseif($properties[2]) {
				$reason = $properties[2]; //might be an error message
			} elseif($this->status == 'new') {
				$reason = 'new'; //a TB we just found
			} else {
				$reason = 'unknown'; //something mysterious happend
			}
			
			$trackbacks[] = array(
				'url'    => $properties[0],
				'status' => $properties[1],
				'reason' => $reason
			);	
		}
		
		return $trackbacks;
	}
	
	/**
	 * reverse function of getTrackbackStatus, builds a string to store in the DB
	 * from an array containing URL, status and an optional message
	 * 
	 * @param	array	Trackback status array, with URL, status and errormessage
	 * @return	string
	 */
	function setTrackbackStatus($TBstatus) {
		$TBlist = '';
		
		foreach($TBstatus as $TB) {
			$TBlist .= $TB['url'].'|'.$TB['status'];
			$TBlist .= $TB['reason'] ?  '|'.$TB['reason'] : '';
			$TBlist .= chr(10);
		}
		
		return trim($TBlist);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_be.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_be.php']);
}

?>
