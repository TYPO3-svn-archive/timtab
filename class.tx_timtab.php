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
 * class.tx_timtab.php
 *
 * Class which implements methods to connect between tt_news and ve_guestbook,
 * hooks for filling custom markers in these extensions an their templates.
 * Hooks in TYPO3's core are used, too.
 * The extraItemMarkerProcessor can be called by both, tt_news and ve_guestbook.
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
 *   68: class tx_timtab
 *   87:     function main($markerArray, $conf)
 *  102:     function init($markerArray, $conf)
 *  121:     function substituteMarkers()
 *  172:     function count_comments()
 *  187:     function getPostTitle()
 *  203:     function buildPostTitle($title)
 *  222:     function getGravatar()
 *
 *              SECTION: Hook Connectors
 *  268:     function extraItemMarkerProcessor($markerArray, $row, $lConf, $pObject)
 *  286:     function postEntryInsertedProcessor($pObj)
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');

class tx_timtab extends tslib_pibase {
	var $cObj; // The backReference to the mother cObj object set at call time
	// Default plugin variables:
	var $prefixId 		= 'tx_timtab';		// Same as class name
	var $scriptRelPath 	= 'class.tx_timtab.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'timtab';	// The extension key.

	var $conf;
	var $markerArray;
	var $calledBy;
	var $pObj;

	/**
	 * main function which executes all steps
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	array		modified marker array
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
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	void
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
		
		#debug(get_class($GLOBALS['TSFE']->tmpl));
	}

	/**
	 * substitutes markers like count of comments
	 *
	 * @return	void
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
		} else if($this->calledBy == 've_guestbook') {
			$this->markerArray['###BLOG_FORM_REQUIRED###'] = $this->pi_getLL('formRequired');
			$this->markerArray['###BLOG_LEAVE_REPLY###'] = $this->pi_getLL('leaveReply');
			$this->markerArray['###BLOG_NOT_PUBLISHED###'] = $this->pi_getLL('notPublished');
			$this->markerArray['###BLOG_NAME###'] = $this->pi_getLL('commentName');
			$this->markerArray['###BLOG_MAIL###'] = $this->pi_getLL('commentMail');
			$this->markerArray['###BLOG_HOMEPAGE###'] = $this->pi_getLL('commentURL');

			$this->markerArray['###BLOG_COMMENTS_COUNT###'] = $this->pObj->internal['res_count'];
			if($this->pObj->internal['res_count'] == 1) {
				$this->markerArray['###BLOG_RESPONSES###']	= $this->pi_getLL('one_response');
			} else {
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
	 * @return	integer		number of comments for the current post
	 */
	function count_comments() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid',
			'tx_veguestbook_entries',
			'uid_tt_news = '.$this->conf['data']['uid'].$this->local_cObj->enableFields('tx_veguestbook_entries')
		);

		return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
	}

	/**
	 * gets the post title when called by ve_guestbook
	 *
	 * @return	string		title of the post
	 */
	function getPostTitle() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'title',
			'tt_news',
			'uid = '.$this->pObj->tt_news['tx_ttnews[tt_news]'].$this->local_cObj->enableFields('tt_news')
		);

		return $res[0]['title'];
	}

	/**
	 * builds the title for the post in single view, wraps it with a link
	 *
	 * @param	string		the title of the post
	 * @return	string		title, ready for output
	 */
	function buildPostTitle($title) {
		#debug($this->conf['data']);
		#debug($this->markerArray);
		$addParams  = '&tx_ttnews[tt_news]='.$this->conf['data']['uid'];
		$addParams .= '&tx_ttnews[year]='.$this->pObj->piVars['year'];
		$addParams .= '&tx_ttnews[month]='.$this->pObj->piVars['month'];
		$addParams .= '&tx_ttnews[day]='.$this->pObj->piVars['day'];
		
		#debug($this->pObj->categories);
		$conf = array(
			'parameter' => $GLOBALS['TSFE']->id,
			'ATagParams' => 'rel="bookmark" title="Permanent Link: '.$title.'"',
			'additionalParams' => $addParams,
			'no_cache' => $this->pObj->allowCaching?0:1,
			'useCacheHash' => $this->pObj->allowCaching,
		);

		return $this->local_cObj->typolink($title, $conf);
	}

	/**
	 * builds the URL to get the gravatar from for the comments
	 *
	 * @param	string		the email of the person who left a comment
	 * @return	string		img tag for the gravatar
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

	/***********************************************
	 *
	 * Hook Connectors
	 *
	 **********************************************/

	/**
	 * connects into tt_news and ve_guestbook item marker processing hook and fills our markers
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the current tt_news record
	 * @param	array		the configuration coming from tt_news
	 * @param	object		the parent object calling this method
	 * @return	array		processed marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, $pObj) {
		$this->conf['data'] = $row;
		$this->pObj = $pObj;
		$this->calledBy = $pObj->extKey; //who is calling?

		#debug($pObject->extKey);
		#debug($pObject, 'calling parent object in timtab');

		return $this->main($markerArray, $lConf);
	}

	/**
	 * connects to the hook in ve_guestbook to post process a comment entry
	 * we'll just clear the cache of some pages to keep the comment count updated
	 *
	 * @param	object		parentObject the calling ve_guestbook object
	 * @return	void
	 */
	function postEntryInsertedProcessor($pObj) {
		#$this->cObj = $pObj->cObj;
		$this->init(array(), array());
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->admin = 1;

		$clearCachePages = split(',', $this->conf['clearPageCacheOnUpdate']);
		foreach($clearCachePages as $page) {
			$tce->clear_cacheCmd($page);
		}
		$tce->admin = 0;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab.php']);
}

?>
