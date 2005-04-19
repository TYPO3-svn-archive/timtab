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
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');

/**
 * class.tx_timtab.php
 *
 * A class which implements methods to connect between tt_news and ve_guestbook 
 * and hooks for filling custom markers in these extensions an their templates.
 * The extraItemMarkerProcessor can be called by both, tt_news and ve_guestbook.
 * 
 * $Id$
 *
 * @author Ingo Renner <typo3@ingo-renner.com>
 */
class tx_timtab extends tslib_pibase {
	var $cObj; // The backReference to the mother cObj object set at call time
	// Default plugin variables:
	var $prefixId 		= 'tx_timtab';		// Same as class name
	var $scriptRelPath 	= 'class.tx_timtab.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'timtab';	// The extension key.
	
	var $conf;
	var $markerArray;
	var $calledBy;
	var $callingObj;
	
	/**
	 * main function which executes all steps
	 * 
	 * @param markerArray an array of markers coming from tt_news
	 * @param conf the configuration coming from tt_news
	 * @return the modified marker array
	 */
	function main($markerArray, $conf) {		
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$this->init($markerArray, $conf);
		$this->substituteMarkers();
		
		return $this->markerArray;
	}
	
	/**
	 * initializes the configuration for the extension
	 * 
	 * @param markerArray an array of markers coming from tt_news
	 * @param conf the configuration coming from tt_news
	 */
	function init($markerArray, $conf) {
		$this->pi_loadLL(); // Loading language-labels
				
		// pi_setPiVarDefaults() does not work since we are in a code library 
		// and don't get called as a plugin, so we're getting our conf this way:
		// $this->conf might be set already, so we have to merge both arrays
		$this->conf = array_merge($this->conf, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.']);
		
		$this->markerArray = $markerArray;
		
		#debug($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.'], 'TSFE config for timtab');
		#debug($this->conf, '$this->conf');
	}	
	
	/**
	 * substitutes markers like count of comments
	 */
	function substituteMarkers() {
		if($this->calledBy == 'tt_news') {
			$comment_count = $this->count_comments();
			if($comment_count == 0) {
				$this->markerArray['###BLOG_COMMENTS_COUNT###'] = '';
				$this->markerArray['###BLOG_TEXT_COMMENTS###'] = $this->pi_getLL('no_comments');
			}
			elseif($comment_count == 1) {
				$this->markerArray['###BLOG_COMMENTS_COUNT###'] = 1;
				$this->markerArray['###BLOG_TEXT_COMMENTS###'] = $this->pi_getLL('one_comment');
			}
			else {
				$this->markerArray['###BLOG_COMMENTS_COUNT###'] = $comment_count;
				$this->markerArray['###BLOG_TEXT_COMMENTS###'] = $this->pi_getLL('multiple_comments');
			}			
			
			$this->markerArray['###BLOG_TEXT_CAT###'] = $this->pi_getLL('textCat');
			$this->markerArray['###BLOG_POST_TITLE###'] = $this->buildPostTitle($this->conf['data']['title']);		
		}
		elseif($this->calledBy == 've_guestbook') {
			$this->markerArray['###BLOG_FORM_REQUIRED###'] = $this->pi_getLL('formRequired');
			$this->markerArray['###BLOG_LEAVE_REPLY###'] = $this->pi_getLL('leaveReply');
			$this->markerArray['###BLOG_NOT_PUBLISHED###'] = $this->pi_getLL('notPublished');
			$this->markerArray['###BLOG_NAME###'] = $this->pi_getLL('commentName');
			$this->markerArray['###BLOG_MAIL###'] = $this->pi_getLL('commentMail');
			$this->markerArray['###BLOG_HOMEPAGE###'] = $this->pi_getLL('commentURL');
		
			$this->markerArray['###BLOG_COMMENTS_COUNT###'] = $this->callingObj->internal['res_count'];
			if($this->callingObj->internal['res_count'] == 1) {
				$this->markerArray['###BLOG_RESPONSES###']	= $this->pi_getLL('one_response');
			}
			else {
				$this->markerArray['###BLOG_RESPONSES###']	= $this->pi_getLL('multiple_responses');
			}
			$this->markerArray['###BLOG_POST_TITLE###'] = $this->getPostTitle();
			$this->markerArray['###BLOG_COMMENT_GRAVATAR###'] = $this->getGravatar();
			
			if(!empty($this->conf['data']['homepage'])) {
				$this->markerArray['###BLOG_COMMENTER_NAME###'] = '<a href="'.$this->conf['data']['homepage'].'" rel="external nofollow">'.$this->conf['data']['firstname'].'</a>';
			} else {
				$this->markerArray['###BLOG_COMMENTER_NAME###'] = $this->conf['data']['firstname'];
			}
			
			
			#debug($this->conf['data']);
		}
	}
	
	/**
	 * counts comments for the current post
	 * 
	 * @return number of comments for the current post
	 */
	function count_comments() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery( 
			'uid',
			'tx_veguestbook_entries',
			'uid_tt_news = '.$this->conf['data']['uid'].' AND deleted = 0' 
		);
		
		return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
	}
	
	/**
	 * gets the post title when called by ve_guestbook
	 * 
	 * @return the title of the post
	 */
	function getPostTitle() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows( 
			'title',
			'tt_news',
			'uid = '.$this->callingObj->tt_news['tx_ttnews[tt_news]'].' AND deleted = 0' 
		);
	
		return $res[0]['title'];
	}
	
	/**
	 * builds the title for the post in single view, wraps it with a link
	 * 
	 * @param title the title of the post
	 * @return the title, ready for output
	 */
	function buildPostTitle($title) {
		$conf = array(
			'parameter' => $GLOBALS['TSFE']->id,
			'ATagParams' => 'rel="bookmark" title="Permanent Link: '.$title.'"',
			'additionalParams' => '&tx_ttnews[tt_news]='.$this->conf['data']['uid'],
			'no_cache' => $this->callingObj->allowCaching?0:1,
			'useCacheHash' => $this->callingObj->allowCaching,
		);
		
		return $this->local_cObj->typolink($title, $conf);
	}
	
	/**
	 * builds the URL to get the gravatar from for the comments
	 * 
	 * @param email the email of the person who left a comment
	 * @return the img tag for the gravatar
	 * @see http://www.gravatar.com
	 */
	function getGravatar() {
		$gravatar  = '<img src="http://www.gravatar.com/avatar.php?gravatar_id=';
		$gravatar .= md5($this->conf['data']['email']);
		
		if(!empty($this->conf['gravatar.']['defaultImg'])) {
			$gravatar .= '&amp;default='.urlencode($this->conf['gravatar.']['defaultImg']);
		}
		
		$size = '';
		if($this->conf['gravatar.']['size'] != 80) {
			$gravatar .= '&amp;size='.$this->conf['gravatar.']['size'];
			$size = ' width="'.$this->conf['gravatar.']['size'].'" height="'.$this->conf['gravatar.']['size'].'"';
		}
		
		if($this->conf['gravatar.']['rating'] != 0) {
			$gravatar .= '&amp;rating='.$this->conf['gravatar.']['rating'];
		}
		
		if($this->conf['gravatar.']['border'] != 0) {
			$gravatar .= '&amp;border='.$this->conf['gravatar.']['border'];
		}
		
		$gravatar .= '"'; //closing href attribute
		
		if(!empty($this->conf['gravatar.']['class'])) {
			$gravatar .= ' class="'.$this->conf['gravatar.']['class'].'"';
		}
		
		return $gravatar.$size.' alt="" />';
	}
		
	/**
	 * connects into tt_news and ve_guestbook item marker processing hook and fills our markers
	 * 
	 * @param markerArray an array of markers coming from tt_news
	 * @param row the current tt_news record
	 * @param lConf the configuration coming from tt_news
	 * @param parentObject the parent tt_news object calling this method
	 * @return the processed marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, $callingObject) {
		$this->conf['data'] = $row;
		$this->callingObj = $callingObject;
		$this->calledBy = $callingObject->extKey; //who is calling?
		
		#debug($callingObject->extKey);		
		#debug($callingObject, 'calling parent object in timtab');
		
		return $this->main($markerArray, $lConf);
	}
	
	/**
	 * connects to the hook in ve_guestbook to post process a comment entry
	 * we'll just clear the cache of some pages to keep the comment count updated
	 * 
	 * @param parentObject the calling ve_guestbook object
	 * @return void
	 */
	function postEntryInsertedProcessor($parentObject) {
		#$this->cObj = $parentObject->cObj;
		$this->init(array(), array());		
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->admin = 1;
		
		$clearCachePages = split(',', $this->conf['clearPageCacheOnComment']);
		foreach($clearCachePages as $page) {
			$tce->clear_cacheCmd($page);
		}		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab.php']);
}

?>
