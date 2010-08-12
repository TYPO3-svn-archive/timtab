<?php
/***************************************************************
*  Copyright notice
*
*  (c) 	2005 Ingo Renner (typo3@ingo-renner.com)
*				2010 Lina Wolf (2010@lotypo3.de)
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
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Ingo Renner <typo3@ingo-renner.com>
 * @author Lina Wolf <2010@lotypo3.de>
 * @author Timo Webler <timo.webler@dkd.de>
 * @version $Id: class.tx_timtab_be.php 5271 2007-04-03 08:36:47Z flyguide $
 */

$pathTimtab = t3lib_extMgm::extPath('timtab');
if (!defined('PATH_tslib')) {
	define('PATH_tslib', t3lib_extMgm::extPath('cms') . 'tslib/');
}
require_once($pathTimtab . 'lib/class.tx_timtab_trackback.php');
require_once($pathTimtab . 'lib/class.tx_timtab_pingback.php');
require_once($pathTimtab . 'lib/class.tx_timtab_lib.php');
require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_t3lib . 'class.t3lib_timetrack.php');
require_once(PATH_t3lib . 'class.t3lib_userauth.php');
require_once(PATH_tslib . 'class.tslib_feuserauth.php');
require_once(PATH_tslib . 'class.tslib_fe.php');

$TT = t3lib_div::makeInstance('t3lib_timeTrack');
$TT->start();

/**
 * class which implements methods to connect to hooks in TCEmain for processinng of trackbacks
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Ingo Renner <typo3@ingo-renner.com>
 * @author Lina Wolf <2010@lotypo3.de>
 * @author Timo Webler <timo.webler@dkd.de>
 */
class tx_timtab_Be {

	/**
	 * the db status action
	 *
	 * @var string
	 */
	protected $status;

	/**
	 * telling us which table the record belongs to, we will process tt_news records only
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * database record
	 *
	 * @var array
	 */
	protected $post;

	/**
	 * the parent object (TCEmain)
	 *
	 * @var t3lib_TCEmain
	 */
	protected $pObj;

	/**
	 * initialize the class
	 *
	 * @param	string	$status	not relevant here
	 * @param	string	$table	telling us which table the record belongs to, we will process tt_news records only
	 * @param	integer	$id	record uid
	 * @param	array	$fieldArray	database record
	 * @param	t3lib_TCEmain	$pObj	the parent object (TCEmain)
	 * @return void
	 */
	protected function init($status, $table, $id, $fieldArray, t3lib_TCEmain $pObj) {
		$this->status = $status;
		$this->table  = $table;
		$this->post   = $fieldArray;
		$this->pObj   = $pObj;

		$this->post['uid'] = $this->getPostId($id);
	}

	/**
	 * get the post uid
	 *
	 * @param integer	$id	record uid
	 * @return integer
	 */
	protected function getPostId($id) {
		$postId = $id;

		if ($this->status == 'new') {
			if (!$this->pObj->substNEWwithIDs[$id]) {
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
	protected function getFullPost() {
		if ($this->status == 'update') {
			$currentPost = $this->post;

			$post = tx_timtab_Lib::getPost($this->post['uid']);
			$post = t3lib_div::array_merge_recursive_overrule(
				$post,
				$currentPost
			);

			$this->post = $post;
		}
	}

	/**
	 * checks wether the current tt_news record is a blog post
	 *
	 * @return	boolean	returns true if record is a blog post, FALSE otherwise
	 */
	protected function isBlogPost() {
		$check = FALSE;

		if ($this->table != 'tt_news') return $check;

		if (isset($this->post['type']) && $this->post['type'] == 3) {
			$check = TRUE;
		} elseif (!isset($this->post['type']) && $this->status == 'update') {
			$post = tx_timtab_Lib::getPost($this->post['uid']);
			$post['type'] == 3 ? $check = TRUE : $check = FALSE;
		}

		return $check;
	}

	/**
	 * initializes and return the configuration for the extension as we need the TS setup
	 * like blog title and timeouts for trackback in the BE, too
	 *
	 * @return	array
	 */
	protected function getTsfeConfig() {

		$pageUid = intval(t3lib_div::_GP('popViewId'));
		//we need a nearly whole TSFE to get the plugin setup
		//and to create correct source URLs
		$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sysPageObj->getRootLine($pageUid);
		$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($rootLine);
		$TSObj->generateConfig();

		$config = array_merge(
			$TSObj->setup['plugin.']['tx_timtab.'],
			$TSObj->setup['plugin.']['tx_timtab_pi2.']
		);

		//free some memory
		unset($TSObj);
		unset($rootLine);
		unset($sysPageObj);

		return $config;
	}

	/**
	 * pre processing of posts, detecting trackback URLs and saving
	 * them into $fieldArray so that they get stored into the DB
	 * and we can ping them afterwards when saving was successful
	 *
	 * @param	string	$status	not relevant here
	 * @param	string	$table	telling us which table the record belongs to, we will process tt_news records only
	 * @param	integer	$id	record uid
	 * @param	array	&$fieldArray	database record
	 * @param	t3lib_TCEmain	$pObj	the parent object (TCEmain)
	 * @return	void
	 */
	public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, t3lib_TCEmain $pObj) {
		$this->init($status, $table, $id, $fieldArray, $pObj);

		if ($table == 'tt_news') {
			if ($this->isBlogPost()) {
				$this->getFullPost();

				//find trackbacks
				$tb = t3lib_div::makeInstance('tx_timtab_Trackback');
				$fieldArray['tx_timtab_trackbacks'] = $tb->getNewTrackbackField(
					$this->status,
					$this->post['tx_timtab_trackbacks'],
					$this->post['bodytext']
				);
			}
		}
	}

	/**
	 * post processing of tt_news entries, sending pings
	 *
	 * @param	string	$status	not relevant here
	 * @param	string	$table	telling us which table the record belongs to, we will process tt_news records only
	 * @param	integer	$id	record uid
	 * @param	array	$fieldArray	database record
	 * @param	t3lib_TCEmain	$pObj	the parent object (TCEmain)
	 * @return	void
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, t3lib_TCEmain $pObj) {
		$this->init($status, $table, $id, $fieldArray, $pObj);

		if ($this->isBlogPost()) {
			$this->getFullPost();

			if ($this->post['hidden'] != 1) {
				//send pings
				$config = $this->getTsfeConfig();

				$tb = t3lib_div::makeInstance('tx_timtab_Trackback');
				$tb->initSend($config, $this->post);
				$tb->sendPings($this->post['tx_timtab_trackbacks']);


				$pb = t3lib_div::makeInstance('tx_timtab_Pingback');
				$pb->initSend($config, $this->post);
				$pb->sendPings($this->post['tx_timtab_pingback']);

				tx_timtab_Lib::clearPageCache($config['clearPageCacheOnUpdate']);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/class.tx_timtab_be.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/class.tx_timtab_be.php']);
}
?>