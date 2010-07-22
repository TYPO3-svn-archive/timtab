<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Ingo Renner (typo3@ingo-renner.com)
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
 * Pingback class for the TIMTAB extension, the majority of the code
 * is taken from wordpress
 *
 * $Id$
 *
 * @author    Ingo Renner <typo3@ingo-renner.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   45: class tx_timtabb_trackback
 *   47:     function pingSent()
 *   56:     function discoverPingbackServerURI()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_timtab_pingback {
	var $prefixId = 'tx_timtab_pingback';        // Same as class name
    var $scriptRelPath = 'class.tx_timtab_pingback.php';    // Path to this script relative to the extension dir.
    var $extKey = 'timtab';    // The extension key.
   
   /**
	 * Configuration array of timtab
	 */
	var $conf;
	
	/**
	 * array representing tt_news post
	 */
	var $post;
	var $encoding;
	var $timeout;
	var $blogName;
    
   /**
	 * initialization for sending trackback pings in BE
	 *
	 * @param	array	conf 			the configuration of timtab
	 * @param	array	fullPost 	the current tt_news record if available
	 * @return	void
	 */
	function initSend($config, $fullPost) {
		$this->conf = $config;
		$this->post = $fullPost;
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		$this->encoding = 'UTF-8';
		$this->timeout  = $this->conf['connectionTimeout'];
		$this->blogName = $this->conf['title'];
	}

	function sendPings() {
		$pingUris = $this->discoverPingbackUri($this->post['bodytext']);
		if($this->conf['']) {
			$pingUris[] = $this->conf[''];
		}
	}
	
	/**
	 * Search content for links, and search found links for trackback URLs.
	 *
	 * @param	string		content to parse for trackback links
	 * @return	array		Trackback URLs.
	 */
	function discoverPingbackUri($content) {
		// Get a list of UNIQUE links from text...
		#$reg_exp = '/(http)+(s)?:(\\/\\/)((\\w|\\.)+)(\\/)?(\\S+)?/i';
		$reg_exp = '/(?:http|https)(?::\/\/)(?:[^\s<>]+)/i';

		// Make sure each link ends with [space]
		$content = str_replace('www.', 'http://www.', $content);
		$content = str_replace('http://http://', 'http://', $content);
		$content = str_replace('https://http://', 'https://', $content);
		$content = str_replace('"', ' "', $content);
		$content = str_replace('\'', ' \'', $content);
		$content = str_replace('>', ' >', $content);

		// Create an array with unique links
		$uri_array = array();
		$subpatterns = array();
		if (preg_match_all($reg_exp, strip_tags($content, '<a><link><LINK>'), $subpatterns, PREG_PATTERN_ORDER)) {

			foreach($subpatterns[0] as $key => $link) {
				$uri_array[] = trim($link, " \t\n\r\0\x0B,.:;");
			}
			$uri_array = array_unique($uri_array);
		}
		unset($key, $link);

		// Get the trackback URIs from those links...
		$rdf_array = array();
		foreach($uri_array as $key => $link) {
			if ($link_content = t3lib_div::getURL($link)) {
				$link_rdf = array();
				preg_match_all('/(<rdf:RDF.*?<\/rdf:RDF>)/smi', $link_content, $link_rdf, PREG_SET_ORDER);

				for ($i = 0; $i < count($link_rdf); $i++) {
					if (preg_match('|dc:identifier="' . preg_quote($link) . '"|ms', $link_rdf[$i][1])) {
						$rdf_array[] = trim($link_rdf[$i][1]);
					}
				}
			}
		}

		// extract trackback URIs
		$tb_array = array();
		$subpatterns = array();
		if (!empty($rdf_array)) {
			for ($i = 0; $i < count($rdf_array); $i++) {
				if (preg_match('/trackback:ping="([^"]+)"/', $rdf_array[$i], $subpatterns)) {
					$tb_array[] = (string) trim($subpatterns[1]);
				}
			}
		}

		return $tb_array;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function discoverPingbackServerURI() {

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_pingback.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_pingback.php']);
}

?>
