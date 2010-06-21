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
 * class.tx_timtab_fe.php
 *
 * Class which implements methods to connect between tt_news and ve_guestbook,
 * hooks for filling custom markers in these extensions an their templates.
 * Hooks in TYPO3's core are used, too.
 * The extraItemMarkerProcessor can be called by both, tt_news and ve_guestbook.
 *
 * $Id: class.tx_timtab_fe.php 4157 2006-11-27 01:25:54Z flyguide $
 *
 * @author Ingo Renner <typo3@ingo-renner.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   65: class tx_timtab_fe extends tslib_pibase
 *   84:     function main($markerArray, $conf)
 *   99:     function init($markerArray, $conf)
 *  124:     function substituteMarkers()
 *  219:     function count_comments()
 *  234:     function getCurrentPost()
 *  250:     function buildPostTitle($title)
 *  274:     function getGravatar()
 *
 *              SECTION: Hook Connectors
 *  321:     function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj)
 *  335:     function postEntryInsertedProcessor($pObj)
 *  365:     function clearPageCache()
 *
 * TOTAL FUNCTIONS: 10
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

$PATH_timtab = t3lib_extMgm::extPath('timtab');
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');
require_once($PATH_timtab.'class.tx_timtab_trackback.php');

class tx_timtab_fe extends tslib_pibase {
	var $cObj; // The backReference to the mother cObj object set at call time
	// Default plugin variables:
	var $prefixId 		= 'tx_timtab_fe';		// Same as class name
	var $scriptRelPath 	= 'class.tx_timtab_fe.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'timtab';	// The extension key.

	var $pObj;
	var $conf;
	var $markerArray;
	var $calledBy;

	/**
	 * main function which executes all steps
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	array		modified marker array
	 */
	function main($markerArray, $conf) {		
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
		$this->cObj = t3lib_div::makeInstance('tslib_cObj'); // local cObj.
		$this->pi_loadLL(); // Loading language-labels

		// pi_setPiVarDefaults() does not work since we are in a code library
		// and don't get called as a plugin, so we're getting our conf this way:
		// $this->conf might be set already, so we have to merge both arrays
		if(!is_array($this->conf)) {
			$this->conf = array();
		}
		$this->conf = array_merge($this->conf, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.']);

		$this->markerArray = $markerArray;

		//init numbering comments
		$pValKey = $this->pObj->cObj->currentValKey;
		$this->pObj->cObj->currentValKey = 'commentNum';
		
		/*
		if($this->calledBy == 've_guestbook' && !$this->pObj->cObj->getCurrentVal()) {
				$this->pObj->cObj->setCurrentVal(0);
		}
		*/

		$this->pObj->cObj->currentValKey = $pValKey;
	}

	/**
	 * substitutes markers like count of comments
	 *
	 * @return	void
	 */
	function substituteMarkers() {
		if($this->calledBy == 'tt_news') {
			
			//trackback
			
			$tb = t3lib_div::makeInstance('tx_timtab_trackback');
			$tb->initFe($this->conf, $this->conf['data']);
			$plink  = $tb->getPermalink();
			$tbURL  = $tb->getTrackbackURL();
			$rdf    = $tb->getEmbeddedRdf($plink, $tbURL);
			$tbLink = $tb->getTrackbackLink();
			
			$this->markerArray['###BLOG_TRACKBACK_RDF###']  = $rdf;
			$this->markerArray['###BLOG_TRACKBACK_LINK###'] = $tbLink;
			$this->markerArray['###BLOG_TRACKBACK_URL###']  = $tbURL;

		} 
		/*
		elseif($this->calledBy == 've_guestbook') {

			$tt_news = $this->getCurrentPost();

			//deactivated comments
			if(!$tt_news['tx_timtab_comments_allowed'] && $this->pObj->status == 'displayForm' && $this->isBlogComment()) {
				$this->pObj->templateCode = '<!-- ###TEMPLATE_FORM### --> ###BLOG_COMMENTS_DEACTIVATED### <!-- ###TEMPLATE_FORM### -->';
				$this->markerArray['###BLOG_COMMENTS_DEACTIVATED###'] = $this->pi_getLL('commentsDeactivated');
			}

			$this->markerArray['###BLOG_FORM_REQUIRED###'] = $this->pi_getLL('formRequired');
			$this->markerArray['###BLOG_LEAVE_REPLY###']   = $this->pi_getLL('leaveReply');
			$this->markerArray['###BLOG_NOT_PUBLISHED###'] = $this->pi_getLL('notPublished');
			$this->markerArray['###BLOG_NAME###']          = $this->pi_getLL('commentName');
			$this->markerArray['###BLOG_MAIL###']          = $this->pi_getLL('commentMail');
			$this->markerArray['###BLOG_HOMEPAGE###']      = $this->pi_getLL('commentURL');

			$this->markerArray['###BLOG_COMMENT_UID###']    = $this->conf['data']['uid'];
			if($this->pObj->internal['res_count'] == 1) {
				$this->markerArray['###BLOG_RESPONSES###']	= $this->pi_getLL('one_response');
			} else {
				$this->markerArray['###BLOG_RESPONSES###']	= $this->pi_getLL('multiple_responses');
			}
			$this->markerArray['###BLOG_POST_TITLE###']     = $tt_news['title'];
			$this->markerArray['###BLOG_COMMENT_GRAVATAR###']   = $this->getGravatar();

			if(!empty($this->conf['data']['homepage'])) {
				$this->markerArray['###BLOG_COMMENTER_NAME###'] = '<a href="'.$this->conf['data']['homepage'].'" rel="external">'.$this->conf['data']['firstname'].'</a>';
			} else {
				$this->markerArray['###BLOG_COMMENTER_NAME###'] = $this->conf['data']['firstname'];
			}
						
			$comment_count = $this->pObj->internal['res_count'];
			if($comment_count == 0) {
				$this->markerArray['###BLOG_COMMENTS_COUNT###'] = '';
				$this->markerArray['###BLOG_TEXT_COMMENTS###']  = $this->pi_getLL('no_comments');
			}
			elseif($comment_count == 1) {
				$this->markerArray['###BLOG_COMMENTS_COUNT###'] = 1;
				$this->markerArray['###BLOG_TEXT_COMMENTS###']  = $this->pi_getLL('one_comment');
			}
			else {
				$this->markerArray['###BLOG_COMMENTS_COUNT###'] = $comment_count;
				$this->markerArray['###BLOG_TEXT_COMMENTS###']  = $this->pi_getLL('multiple_comments');
			}

			//numbering comments
			$pValKey = $this->pObj->cObj->currentValKey;
			$this->pObj->cObj->currentValKey = 'commentNum';

			$commentNum = $this->pObj->cObj->getCurrentVal() + 1;
			$this->markerArray['###BLOG_COMMENT_NUM###'] = $commentNum;
			$this->pObj->cObj->setCurrentVal($commentNum);

			$this->pObj->cObj->currentValKey = $pValKey;

			//remember the visitors data
			$this->markerArray['###BLOG_REMEMBER_YES###']     = $this->pi_getLL('yes');
			$this->markerArray['###BLOG_REMEMBER_NO###']      = $this->pi_getLL('no');
			$this->markerArray['###BLOG_REMEMBER_VISITOR###'] = $this->pi_getLL('rememberInfo');

			if(isset($_COOKIE['comment_info'])) {
				$userInfo = explode('|', $_COOKIE['comment_info']);

				$this->markerArray['###VALUE_FIRSTNAME###'] = $userInfo[0];
				$this->markerArray['###VALUE_EMAIL###']     = $userInfo[1];
				$this->markerArray['###VALUE_HOMEPAGE###']  = $userInfo[2];
			}
			
			$tb = t3lib_div::makeInstance('tx_timtab_trackback');
			$tb->initFe($this->conf, $this->conf['data']);
			$this->markerArray['###BLOG_TRACKBACK_URL###'] = $tb->getTrackbackURL();
			

		}
		*/
	}
	
	/**
	 * Hook called for each comment item
	 * You can set additional markers here
	 *
	 * borrowed from from comments_gravator, Michael Cannon
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	array		modified marker array
	 */
	function comments_getComments($params, $pObj) {
		$this->init();

		$markers = $params['markers'];
		$markers['###GRAVATAR###'] = '';

		if ($this->conf['gravatar.']['enable'] ) {

			$row = $params['row'];
	
			$email = $row['email'];
	
			$name = array();
			$name[] = $row['firstname'];
			$name[] = $row['lastname'];
			$name = trim(implode(' ', $name));
	
			// generate gravatar
			$gravatarImage = $email;
	
			// borrowed from t3blog/pi1/widgets/blogList/class.blogList.php
			// Default needed if user don't have a gravatar and don't have a local pic, but email is stated
			$default 	= (! $this->conf['gravatar.']['defaultIcon'])
				? 'http://'. $_SERVER['HTTP_HOST']. '/'.  t3lib_extMgm::siteRelPath($this->extKey). 'res/buttons/nopic_50_f.jpg'
				: $this->conf['gravatar.']['defaultIcon'];
	
			$size 		= $this->conf['gravatar.']['iconSize'] ? $this->conf['gravatar.']['iconSize'] : 48;
			$class 		= $this->conf['gravatar.']['class'] ? $this->conf['gravatar.']['class'] : '';
	
			$grav_url 	= 'http://www.gravatar.com/avatar/'. md5($email).	'?d='. urlencode($default).'&amp;s='.intval($size).'&amp;r='.$this->conf['gravatar.']['rating'];
			$gravatar 	= '<img src="'. $grav_url . '" alt="Gravatar: '. $name . '"
			title="Gravatar: '. $name . '. Visit gravatar.com for your own icon" height="' . $size . '" height="' . $size
			. '" class="'.$class.'" />';
	
			$markers['###GRAVATAR###'] = $gravatar;
		}
		if ($this->conf['allowSafeTags'] ) {
			// allow safer tags in comments
			$comment	= $markers['###COMMENT_CONTENT###'];
			$search		= array(
				'&lt;pre&gt;'
				, '&lt;/pre&gt;'
				, '[pre]'
				, '[/pre]'
				, '&lt;code&gt;'
				, '&lt;/code&gt;'
				, '[code]'
				, '[/code]'
				, '&lt;blockquote&gt;'
				, '&lt;/blockquote&gt;'
				, '[blockquote]'
				, '[/blockquote]'
				, '&lt;blockquote&gt;'
				, '&lt;/blockquote&gt;'
				, '[blockquote]'
				, '[/blockquote]'
				, '&lt;p&gt;'
				, '&lt;/p&gt;'
				, '[p]'
				, '[/p]'
				, '&lt;ul&gt;'
				, '&lt;/ul&gt;'
				, '[ul]'
				, '[/ul]'
				, '&lt;ol&gt;'
				, '&lt;/ol&gt;'
				, '[ol]'
				, '[/ol]'
				, '&lt;li&gt;'
				, '&lt;/li&gt;'
				, '[li]'
				, '[/li]'
				, '&lt;b&gt;'
				, '&lt;/b&gt;'
				, '[b]'
				, '[/b]'
				, '&lt;i&gt;'
				, '&lt;/i&gt;'
				, '[i]'
				, '[/i]'
				, '&lt;cite&gt;'
				, '&lt;/cite&gt;'
				, '[cite]'
				, '[/cite]'
				, '&lt;h2&gt;'
				, '&lt;/h2&gt;'
				, '[h2]'
				, '[/h2]'
				, '&lt;h3&gt;'
				, '&lt;/h3&gt;'
				, '[h3]'
				, '[/h3]'
			);
			$replace	= array(
				'<pre>'
				, '</pre>'
				, '<pre>'
				, '</pre>'
				, '<code>'
				, '</code>'
				, '<code>'
				, '</code>'
				, '<blockquote>'
				, '</blockquote>'
				, '<blockquote>'
				, '</blockquote>'
				, '<p>'
				, '</p>'
				, '<p>'
				, '</p>'
				, '<ul>'
				, '</ul>'
				, '<ul>'
				, '</ul>'
				, '<ol>'
				, '</ol>'
				, '<ol>'
				, '</ol>'
				, '<li>'
				, '</li>'
				, '<li>'
				, '</li>'
				, '<b>'
				, '</b>'
				, '<b>'
				, '</b>'
				, '<i>'
				, '</i>'
				, '<i>'
				, '</i>'
				, '<cite>'
				, '</cite>'
				, '<cite>'
				, '</cite>'
				, '<h2>'
				, '</h2>'
				, '<h2>'
				, '</h2>'
				, '<h3>'
				, '</h3>'
				, '<h3>'
				, '</h3>'
			);
			$comment	= str_replace($search, $replace, $comment);
			$markers['###COMMENT_CONTENT###']	= $comment;
		}

		return $markers;
	}

	/**
	 * counts comments for the current post
	 *
	 * @return	integer		number of comments for the current post
	 
	function count_comments() {
		$select = 'uid';
		$table = 'tx_veguestbook_entries';
		// Entries on page 0 should not be counted as they are never displayed
		$where = 'tx_veguestbook_entries.uid_tt_news = '.$this->conf['data']['uid'].$this->cObj->enableFields('tx_veguestbook_entries').
			' AND tx_veguestbook_entries.pid > 0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);

		return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
	}
	*/

	/**
	 * gets the current post when called by ve_guestbook
	 *
	 * @return	array
	 
	function getCurrentPost() {
		$tt_news = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tt_news',
			'uid = '.intval($this->pObj->tt_news['tx_ttnews[tt_news]']).$this->cObj->enableFields('tt_news')
		);

		return $tt_news[0];
	}
	*/

	/**
	 * builds the URL to get the gravatar from for the comments
	 *
	 * @param	string		the email of the person who left a comment
	 * @return	string		img tag for the gravatar
	 * @see http://www.gravatar.com
	 
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

		$alt   = 'alt="Gravatar: '.$this->conf['data']['firstname'].'"';
		$title = 'title="'.$this->pi_getLL('gravatar_this_is').' '
				.$this->conf['data']['firstname'].' "';

		return $gravatar.$size.' '.$alt.' '.$title.' />';
	}
	*/

	/***********************************************
	 *
	 * Hook Connectors
	 *
	 **********************************************/

	/**
	 * connects into tt_news and ve_guestbook item marker processing hook
	 * and fills our markers
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the current tt_news record
	 * @param	array		the configuration coming from tt_news
	 * @param	object		the parent object calling this method
	 * @return	array		processed marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$this->conf         = array();
		$this->conf['data'] = $row;
		$this->pObj         = &$pObj;
		$this->calledBy     = $pObj->extKey; //who is calling?

		return $this->main($markerArray, $lConf);
	}

	/**
	 * connects to the hook in ve_guestbook to post process a comment entry
	 *
	 * @param	object		parentObject the calling ve_guestbook object
	 * @return	void
	 
	function postEntryInsertedProcessor($pObj) {
		$this->init(array(), array());

		//clear page cache for some pages to keep the comment count updated
		$this->clearPageCache();

		//save user data for comment form so he doesn't have to type it in every time
		//only if user wants this and we are allowed to set cookies
		$postData = t3lib_div::_POST('tx_timtab');
		$remember = $postData['remember_visitor'];

		if (!$this->dontSetCookie && $remember) {
			$userInfo = implode('|',
				array(
					$pObj->postvars['firstname'],
					$pObj->postvars['email'],
					$pObj->postvars['homepage'],
				)
			);

			setcookie('comment_info', $userInfo, time() + 3600 * 24 * 90, '/');
		}

	}	
	*/

	/**
	 * checks whether we are are used in a blog environment
	 * 
	 * @return	boolean		true if we are commenting on a post, false otherwise
	 */
	function isBlogComment() {
		$result = false;
		$tt_news = t3lib_div::_GET('tx_ttnews');
		
		if(isset($tt_news['tt_news'])) {
			$result = true;	
		}
		
		return $result;
	}

	/**
	 * explicitly clears cache for the blog page as it is not updating sometimes
	 *
	 * @return	void
	 */
	function clearPageCache() {
		//TODO put this in a class timtab_lib
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->admin = 1;

		$clearCachePages = split(',', $this->conf['clearPageCacheOnUpdate']);
		foreach($clearCachePages as $page) {
			$tce->clear_cacheCmd($page);
		}
		$tce->admin = 0;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_fe.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_fe.php']);
}

?>