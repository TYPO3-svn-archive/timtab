<?php
/***************************************************************
*  Copyright notice
*
*  (c) 	2010 Lina Wolf (2010@lotypo3.de)
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
 * class.tx_timtab_hook_comments.php
 *
 * Implements hooks for tt_news to create additional markers
 *
 * @author Lina Wolf <2010@lotypo3.de>
 */
 
$PATH_timtab = t3lib_extMgm::extPath('timtab');
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');

class tx_timtab_hook_comments extends tslib_pibase {
	
	var $cObj = null;
	
	
	/**
	* Implementation of "closeCommentsAfter"-Hook of Extension "comments"
	* @return 1 ( = 1.1.1970 0:001) if comments are closed, false otherwise
	*/
	function closeComments($params, $pObj) {
		if($params['table'] == 'tt_news' && $params['uid'] > 0) {
			$where = 'uid=' . intval($params['uid']). $pObj->cObj->enableFields($params['table']);
			$rowArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('tx_timtab_comments_allowed', $params['table'], $where); 
			if($rowArray) {
				$row = $rowArray[0];
				if(!$row['tx_timtab_comments_allowed'])
					return 1;
			}
		}
		return false;
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
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_comments_pi1.'];

		$markers = $params['markers'];
		$markers['###GRAVATAR###'] = '';

		if ($conf['timtab.']['gravatar.']['enable'] ) {

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
			$default 	=  $conf['gravatar.']['defaultIcon'];
	
			$size 		= $conf['timtab.']['gravatar.']['iconSize'] ? $conf['timtab.']['gravatar.']['iconSize'] : 48;
			$class 		= $conf['timtab.']['gravatar.']['class'] ? 'class="'.$conf['timtab.']['gravatar.']['class'].'" ' : '';
	
			$grav_url 	= 'http://www.gravatar.com/avatar/'. md5($email).	'?d='. urlencode($default).'&amp;s='.intval($size).'&amp;r='.$conf['timtab.']['gravatar.']['rating'];
			$gravatar 	= '<img src="'. $grav_url . '" alt="Gravatar: '. $name . '"
			title="Gravatar: '. $name . '. Visit gravatar.com for your own icon" height="' . $size . '" height="' . $size
			. '" />';
	
			$markers['###GRAVATAR###'] = $gravatar;
		}
		if ($conf['timtab.']['allowSafeTags'] ) {
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/class.tx_timtab_hook_comments.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/class.tx_timtab_hook_comments.php']);
}


?>