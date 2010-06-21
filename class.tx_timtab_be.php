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
 * class which implements methods to connect to hooks in TCEmain for processinng of trackbacks
 *
 * $Id: class.tx_timtab_be.php 5271 2007-04-03 08:36:47Z flyguide $
 *
 * @author Ingo Renner <typo3@ingo-renner.com>
 */

$PATH_timtab = t3lib_extMgm::extPath('timtab');
define('PATH_tslib', PATH_site.TYPO3_mainDir.'sysext/cms/tslib/');
require_once($PATH_timtab.'class.tx_timtab_trackback.php');
#require_once($PATH_timtab.'class.tx_timtab_pingback.php');
require_once($PATH_timtab.'class.tx_timtab_lib.php');
require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_t3lib.'class.t3lib_timetrack.php');
require_once(PATH_t3lib.'class.t3lib_userauth.php');
require_once(PATH_tslib.'class.tslib_feuserauth.php');
require_once(PATH_tslib.'class.tslib_fe.php');

$TT = new t3lib_timeTrack;
$TT->start();

class tx_timtab_be {
	var $prefixId      = 'tx_timtab_be';		// Same as class name
	var $scriptRelPath = 'class.tx_timtab_be.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'timtab';	// The extension key.

	var $status;
	var $table;
	var $post;
	var $pObj;
	
	var $hookUsed; //for debugging only


	function init($status, $table, $id, $fieldArray, $pObj) {
		$this->status = $status;
		$this->table  = $table;
		$this->post   = $fieldArray;
		$this->pObj   = $pObj;
		
		$this->post['uid'] = $this->getPostId($id);
	}

	function getPostId($id) {
		$postId = $id;
		
		if($this->status == 'new') {
			if(!$this->pObj->substNEWwithIDs[$id]) {
				//postProcessFieldArray
				$postId = 0;
			} else {
				//afterDatabaseOperations
				$postId = $this->pObj->substNEWwithIDs[$id];
			}
		}
		
		return $postId;
	}
	
	/**
	 * get's the full post if status is update as the post is completly
	 * available already when status is new. The post from the DB is also
	 * combined with the current updates from $fieldArray
	 * 
	 * @return	void
	 */
	function getFullPost() {
		if($this->status == 'update') {
			$currentPost = $this->post;
			
			$post = tx_timtab_lib::getPost($this->post['uid']);
			$post = t3lib_div::array_merge_recursive_overrule(
				$post, 
				$currentPost
			);
			
			$this->post = $post;			
		}	
	}
	
	/**
	 * checks whether the current tt_news record is a blog post
	 *
	 * @return	boolean		returns true if record is a blog post, false otherwise
	 */
	function isBlogPost() {
		$check = false;
		
		if($this->table != 'tt_news') return $check;
		
		if(isset($this->post['type']) && $this->post['type'] == 3) {
			$check = true;
		} elseif(!isset($this->post['type']) && $this->status == 'update') {
			$post = tx_timtab_lib::getPost($this->post['uid']);
			$post['type'] == 3 ? $check = true : $check = false;
		}
		
		return $check;
	}

	/**
	 * initializes the configuration for the extension as we need the TS setup
	 * like blog title and timeouts for trackback in the BE, too
	 *
	 * @return	void
	 */
	function getTsfeConfig() {
		global $TSFE;  //get rid of this sometime

		$pid = intval(t3lib_div::_GP('popViewId'));

		//we need a nearly whole TSFE to get the plugin setup 
		//and to create correct source URLs
		if(!is_object($GLOBALS['TSFE'])) {
			$TSFE = t3lib_div::makeInstance('tslib_fe',
				$GLOBALS['TYPO3_CONF_VARS'],	//TYPO3_CONF_VARS
				$pid,							//pid	
				'',								//type
				0,								//no_cache
				'',								//cHash
				'',								//jumpurl
				'',								//MP
				''								//RDCT
			);
			$TSFE->forceTemplateParsing = 1;
			$TSFE->showHiddenPage = false;
			$TSFE->initFEuser();
			$TSFE->fetch_the_id();
			$TSFE->initTemplate();
			$TSFE->getConfigArray();
		}

		$config = array_merge(
			$TSFE->tmpl->setup['plugin.']['tx_timtab.'],
			$TSFE->tmpl->setup['plugin.']['tx_timtab_pi2.']
		);
		
		//free some memory
		unset($TSFE);
		
		return $config;
	}

	/**
	 * pre processing of posts, detecting trackback URLs and saving
	 * them into $fieldArray so that they get stored into the DB 
	 * and we can ping them afterwards when saving was successful
	 *
	 * @param	string		action status: new/update is relevant for us
	 * @param	string		db table
	 * @param	integer		record uid
	 * @param	array		record
	 * @param	object		parent object
	 * @return	void
	 */
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $pObj) {
		$this->hookUsed = 'postProcessFieldArray';
		$this->init($status, $table, $id, $fieldArray, $pObj);
		
		if($this->isBlogPost()) {
			$this->getFullPost();
			
			//find trackbacks
			$tb = t3lib_div::makeInstance('tx_timtab_trackback');
			$fieldArray['tx_timtab_trackbacks'] = $tb->getNewTrackbackField(
				$this->status, 
				$this->post['tx_timtab_trackbacks'],
				$this->post['bodytext']
			);		
		}
	}

	/**
	 * post processing of tt_news entries, sending pings
	 *
	 * @param	string		not relevant here
	 * @param	string		telling us which table the record belongs to, we will process tt_news records only
	 * @param	integer		record uid
	 * @param	array		database record
	 * @param	object		the parent object (TCEmain)
	 * @return	void
	 */
	function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj) {
		$this->hookUsed = 'afterDatabaseOperations';
		$this->init($status, $table, $id, $fieldArray, $pObj);
		
		if($this->isBlogPost()) {
			$this->getFullPost();
			
			if($this->post['hidden'] != 1) {
				//send pings
				$config = $this->getTsfeConfig();
				
				$tb = t3lib_div::makeInstance('tx_timtab_trackback');
				$tb->initSend($config, $this->post);
				$tb->sendPings($this->post['tx_timtab_trackbacks']);
				
				tx_timtab_lib::clearPageCache($config['clearPageCacheOnUpdate']);
			}			
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_be.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_be.php']);
}
?>