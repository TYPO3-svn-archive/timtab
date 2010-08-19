<?php
/***************************************************************
*  Copyright notice
*
*  (c) 	2005 Ingo Renner (typo3@ingo-renner.com)
*		2010 Werner Trunk und Lina Wolf (2010@lotypo3.de)
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

define('TYPE_TRACKBACK', 1);
define('TYPE_PINGBACK', 2);
define('TYPE_TRACKBACK_SPAM', 3);

$pathTimtab = t3lib_extMgm::extPath('timtab');
require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once($pathTimtab . 'pi2/class.tx_timtab_pi2_xmlrpcserver.php');
require_once($pathTimtab . 'lib/class.tx_timtab_trackback.php');


/**
 * Plugin 'webservices' for the 'TIMTAB' extension.
 *
 * @package TYPO3
 * @subpackage timtab
 * @author	Ingo Renner <typo3@ingo-renner.com>
 * @author	Ingo Schommer <me@chillu.com>
 * @author	Werner Trunk
 * @author	Lina Wolf <2010@lotypo3.de>
 * @author	Timo Webler <timo.webler@dkd.de>
 * @version $Id$
 */
class tx_timtab_pi2 extends tslib_pibase {

	/**
	 * Same as class name
	 *
	 * @var string
	 */
	public $prefixId = 'tx_timtab_pi2';

	/**
	 * Path to this script relative to the extension dir.
	 *
	 * @var string
	 */
	public $scriptRelPath = 'pi2/class.tx_timtab_pi2.php';

	/**
	 * The extension key.
	 *
	 * @var string
	 */
	public $extKey = 'timtab';

	/**
	 * Configuring so caching is not expected. This value means that no cHash params are ever set.
	 * We do this, because it's a USER_INT object!
	 *
	 * @var integer
	 */
	public $pi_USER_INT_obj = 1;

	/**
	 * plugin configuration
	 *
	 * @var array
	 */
	public $conf = array();

	/**
	 * tt_news id
	 *
	 * @var integer
	 */
	protected $ttNews;

	/**
	 * post record
	 *
	 * @var array
	 */
	protected $fullPost;

	/**
	 * main function of pi2 decides weather to process a XML-RPC request or Trackback
	 *
	 * @param	string $content ignored
	 * @param	array $conf with configuration for the webservice
	 * @return	string error / sucess code for trackbacks, empty string otherwise
	 */
	public function main($content, $conf) {
		$this->init($conf);

		if ($this->piVars['trackback'] == '1') {
			$content = $this->processTrackback();
		} else {
			if (t3lib_div::compat_version('4.3')) {
				$xmlrpcServer = t3lib_div::makeInstance('tx_timtab_pi2_XmlrpcServer', $this);
			} else {
				//wtweb , nach aendern auf t3lib_div::makeInstance wegen depreceted geht einiges nicht mehr,zB edit exisiting per word
				$className = t3lib_div::makeInstanceClassName('tx_timtab_pi2_XmlrpcServer');
				//wtweb wozu die konstruktion ?
				$xmlrpcServer = new $className($this);
			}
		}
		return $content;
	}

	/**
	 * initializes the configuration for the extension
	 *
	 * @param	array	$conf	configuration array
	 * @return	void
	 */
	protected function init($conf) {
		$this->conf = array_merge($conf, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.']);
		$this->pi_setPiVarDefaults();

		if ($this->piVars['trackback'] == '1') {
			$ttNews = t3lib_div::_GP('tx_ttnews');
			$this->ttNews = intval($ttNews['tt_news']);
		}
	}

	/**
	 * processing of tracbacks, checks for a tt_news uid, whether pings are enabled
	 * for this post, the URL of the backtracker and whether we already have a
	 * ping from that URL
	 *
	 * @return	string XML Code with error or success message
	 */
	protected function processTrackback() {
		$tb = t3lib_div::makeInstance('tx_timtab_Trackback');
		$tb->initSend($this->conf, array('uid', $this->ttNews));
		if (!$this->ttNews || !is_int($this->ttNews)) {
			return $tb->sendResponse(FALSE, 'I really need an ID for this to work.');
		}

		//process trackback
		$tbURL    = (string) t3lib_div::_POST('url');
		$title    = t3lib_div::_POST('title');
		$excerpt  = t3lib_div::_POST('excerpt');
		$blogName = t3lib_div::_POST('blog_name');
		$charset  = t3lib_div::_POST('charset');

		if ($charset) {
			$charset = strtoupper(trim($charset) );
		} else {
			$charset = 'ASCII, UTF-8, ISO-8859-1, JIS, EUC-JP, SJIS';
		}

		if (!empty($tbURL)) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tt_news',
				'uid = ' . intval($this->ttNews)
			);
			$ttNews = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$tb->post = $ttNews;
			// if trackbacks disabled send errormessage
			if (!$ttNews['tx_timtab_ping_allowed']) {
				return $tb->sendResponse(FALSE, 'Sorry, trackbacks are closed for this item.');
			}
			//check for existing link to us - SPAM check
			$permalink = $tb->getPermalink();
			$tbType = TYPE_TRACKBACK;
			if ($this->conf['trackback.']['validate'] && $this->isTrackbackSpam($tbURL, $permalink)) {
				$tbType = TYPE_TRACKBACK_SPAM;
			}

			$title   = htmlspecialchars(strip_tags($title));
			$title   = $GLOBALS['TSFE']->csConv($title, $charset);
			$title   = (strlen($title) > 250) ? substr($title, 0, 250) . '...' : $title;

			$excerpt = strip_tags($excerpt);
			$excerpt = $GLOBALS['TSFE']->csConv($excerpt, $charset);
			$excerpt = (strlen($excerpt) > 255) ? substr($excerpt, 0, 252) . '...' : $excerpt;

			$blogName = $GLOBALS['TSFE']->csConv($blogName, $charset);

			//do we have a ping, already?
			unset($res);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'tx_comments_comments',
				'external_ref = "tt_news_' . intval($ttNews['uid']) . '" AND homepage = \'' . addslashes ($tbURL) . '\''
			);

			$tbEntry = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
			if ($tbEntry) {
				return $tb->sendResponse(FALSE, 'We already have a ping from that URI for this post.');
			} else {
				unset($res);
				$time = time();
				// field get escaped by exec_INSERTquery
				$insertFields = array(
					'pid' => $this->conf['pidStoreComments'],
					'external_ref' => 'tt_news_' . $ttNews['uid'],
					'external_prefix' => 'tx_ttnews',
					'tstamp' => $time,
					'crdate' => $time,
					'firstname' => $blogName,
					'homepage' => $tbURL,
					'content' => $excerpt,
					'approved' => '0',
					'remote_addr' => $_SERVER['REMOTE_ADDR'],
					'tx_timtab_type' => TYPE_TRACKBACK
				);
				//auto approve trackbacks if desired
				if ($this->conf['trackback.']['autoapprove']) {
					$insertFields['approved'] = '1';
				}
					//mark spam
				$saveComment = TRUE;
				if ($tbType == TYPE_TRACKBACK_SPAM) {
					$insertFields['tx_timtab_type'] = TYPE_TRACKBACK_SPAM;

					switch(int($this->conf['trackback.']['spam.']['mark'])) {
						case -2:
							//don't save it at all
							$saveComment = FALSE;
						case -1:
							//mark deleted
							$insertFields['deleted'] = 1;
							break;
						case 0:
							//do nothing
							break;
						case 1:
						default:
							//mark hidden, default
							$insertFields['hidden'] = 1;
					}
				}

				$insertId = 0;
				if ($saveComment) {
					$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
						'tx_comments_comments',
						$insertFields
					);
					$insertId = $GLOBALS['TYPO3_DB']->sql_insert_id($res);
				}
				if ($insertId) {
					return $tb->sendResponse(TRUE);
				} else {
					return $tb->sendResponse(FALSE, 'Something went wrong while saving your ping.');
				}

			}
		} else {
			return $tb->sendResponse(FALSE, 'At least the URL to your entry is required.');
		}
	}


	/**
	 * checks whether the trackback link has a link to us, if not the trackback
	 * is considered SPAM
	 *
	 * @param string $remoteUrl URL of the trackback caler
	 * @param string $permalink URL of our post
	 * @return boolean TRUE if HTML file at $remoteUrl contains a link to $permalink
	 * @author Thomas Hempel?
	 */
	protected function isTrackbackSpam($remoteUrl, $permalink) {
		$remoteContent   = t3lib_div::getURL($remoteUrl);
		$permalinkQuoted = preg_quote($permalink, '/');
		//pattern from TBValidator WP plugin
		$pattern = "/<\s*a.*href\s*=[\"'\s]*" . $permalinkQuoted . "[\"'\s]*.*>.*<\s*\/\s*a\s*>/i";

		return !(preg_match($pattern, $remoteContent));
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2.php']);
}

?>