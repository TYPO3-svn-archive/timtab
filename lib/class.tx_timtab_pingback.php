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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * Pingback class for the TIMTAB extension
 *
 * @package TYPO3
 * @subpackage timtab
 * @author Lina Wolf <2010@lotypo3.de>
 * @author Timo Webler <timo.webler@dkd.de>
 * @version $Id$
 */
class tx_timtab_Pingback {

	/**
	 * Configuration array of timtab
	 *
	 * @var array
	 */
	protected $conf = array();

	/**
	 * array representing tt_news post
	 *
	 * @var array
	 */
	protected $post = array();

	/**
	 * encoding
	 *
	 * @var string
	 */
	protected $encoding;

	/**
	 * connection timeout
	 *
	 * @var string
	 */
	protected $timeout;

	/**
	 * blog name
	 *
	 * @var string
	 */
	protected $blogName;

	/**
	 * initialization for sending trackback pings in BE
	 *
	 * @param	array	$config 	the configuration of timtab
	 * @param	array	$fullPost 	the current tt_news record if available
	 * @return	void
	 */
	public function initSend(array $config, array $fullPost) {
		$this->conf = $config;
		$this->post = $fullPost;
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		$this->encoding = 'UTF-8';
		$this->timeout  = $this->conf['connectionTimeout'];
		$this->blogName = $this->conf['title'];
	}

	/**
	 * sending pings
	 *
	 * @return void
	 */
	public function sendPings() {
		$pingUris = $this->discoverPingbackUri($this->post['bodytext']);
		if ($this->conf['']) {
			$pingUris[] = $this->conf[''];
		}
	}

	/**
	 * Search content for links, and search found links for trackback URLs.
	 *
	 * @param	string	$content	content to parse for trackback links
	 * @return	array				Trackback URLs.
	 */
	protected function discoverPingbackUri($content) {
		// Get a list of UNIQUE links from text...
		//$regExp = '/(http)+(s)?:(\\/\\/)((\\w|\\.)+)(\\/)?(\\S+)?/i';
		$regExp = '/(?:http|https)(?::\/\/)(?:[^\s<>]+)/i';

		// Make sure each link ends with [space]
		$content = str_replace('www.', 'http://www.', $content);
		$content = str_replace('http://http://', 'http://', $content);
		$content = str_replace('https://http://', 'https://', $content);
		$content = str_replace('"', ' "', $content);
		$content = str_replace('\'', ' \'', $content);
		$content = str_replace('>', ' >', $content);

		// Create an array with unique links
		$uriArray = array();
		$subpatterns = array();
		if (preg_match_all($regExp, strip_tags($content, '<a><link><LINK>'), $subpatterns, PREG_PATTERN_ORDER)) {

			foreach ($subpatterns[0] as $key => $link) {
				$uriArray[] = trim($link, " \t\n\r\0\x0B,.:;");
			}
			$uriArray = array_unique($uriArray);
		}
		unset($key, $link);

		// Get the trackback URIs from those links...
		$rdfArray = array();
		foreach ($uriArray as $key => $link) {
			if ($linkContent = t3lib_div::getURL($link)) {
				$linkRdf = array();
				preg_match_all('/(<rdf:RDF.*?<\/rdf:RDF>)/smi', $linkContent, $linkRdf, PREG_SET_ORDER);

				for ($i = 0; $i < count($linkRdf); $i++) {
					if (preg_match('|dc:identifier="' . preg_quote($link) . '"|ms', $linkRdf[$i][1])) {
						$rdfArray[] = trim($linkRdf[$i][1]);
					}
				}
			}
		}

		// extract trackback URIs
		$tbArray = array();
		$subpatterns = array();
		if (!empty($rdfArray)) {
			for ($i = 0; $i < count($rdfArray); $i++) {
				if (preg_match('/trackback:ping="([^"]+)"/', $rdfArray[$i], $subpatterns)) {
					$tbArray[] = (string) trim($subpatterns[1]);
				}
			}
		}

		return $tbArray;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_pingback.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_pingback.php']);
}
?>