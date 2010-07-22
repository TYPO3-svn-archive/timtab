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
 * Trackback class for the TIMTAB extension, the majority of the code
 * is originaly taken from PHP TrackBack,
 * see: http://phptrackback.sourceforge.net
 *
 * $Id: class.tx_timtab_trackback.php 7271 2007-11-22 12:28:38Z flyguide $
 *
 * @author	Ingo Renner <typo3@ingo-renner.com>
 */


$PATH_timtab = t3lib_extMgm::extPath('timtab');
require_once($PATH_timtab.'class.tx_timtab_lib.php');
require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_t3lib.'class.t3lib_div.php');
 
class tx_timtab_trackback {
	var $prefixId = 'tx_timtab_trackback';		// Same as class name
	var $scriptRelPath = 'class.tx_timtab_trackback.php';	// Path to this script relative to the extension dir.
	var $extKey = 'timtab';	// The extension key.
	
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
	
	/**
	 * initialization for use in FE
	 * 
	 * @author Ingo Renner, Lina Wolf
	 * @param	array	conf 			the configuration of timtab
	 * @param	array	fullPost 	the current tt_news record if available
	 * @return	void
	 */
	function initFe($config, $fullPost) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->conf = $config;
		$this->post = $fullPost;
		
		$this->encoding = 'UTF-8';
		$this->timeout  = $this->conf['connectionTimeout'];
		$this->blogName = $this->conf['title'];
	}
	
	function sendPings($tbField) {
		$tbStatus = $this->getTrackbackStatus($tbField);
		
		foreach($tbStatus as $k => $tb) {
			//attempt to ping the trackback URL
			if(!empty($tb['url']) && $tb['status'] == 0) {
				$result = $this->ping($tb['url']);
				if($result['success']) {
					//success
					$tbStatus[$k]['status'] = 1;
					unset($tbStatus[$k]['reason']);
				} else {
					//failed
					$tbStatus[$k]['reason'] = $result['message'];
				}
			}
		}
		
		//update trackback status in tt_news record
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tt_news',
			'uid = '.$this->post['uid'],
			array('tx_timtab_trackbacks' => $this->setTrackbackStatus($tbStatus))
		);
	}

	/**
	 * Sends a trackback ping to a specified trackback URL.
	 * allowing clients to auto-discover the TrackBack Ping URL.
	 *
	 * @param	string		trackback target
	 * @param	string		post title
	 * @param	string		post excerpt
	 * @return	boolean
	 */
	function ping($target)
	{
		$response = '';
		$result   = array();
		$source   = $this->getSourceURL();
		$excerpt  = $this->getExcerpt();

		// Parse target URL
		$bits = parse_url($target);

		if ((isset($bits['query'])) && ($bits['query'] != '')) {
			$bits['query'] = '?' . $bits['query'];
		} else {
			$bits['query'] = '';
		}

		if ((isset($bits['port']) && !is_numeric($bits['port'])) || (!isset($bits['port']))) {
			$bits['port'] = 80;
		}

		$ping  = 'url='.rawurlencode($source);
		$ping .= '&title='.rawurlencode($this->post['title']);
		$ping .= '&blog_name='.rawurlencode($this->blogName);
		$ping .= '&excerpt='.rawurlencode($excerpt);

		$version = explode('.',(TYPO3_version?TYPO3_version:$GLOBALS['TYPO_VERSION']));
		unset($version[2]);
		$version = implode($version,'.');

		$r = "\r\n";
		$request  = 'POST '.$bits['path'].$bits['query'].' HTTP/1.1'.$r;
		$request .= 'Host: '.$bits['host'].$r;
		$request .= 'Content-type: application/x-www-form-urlencoded'.$r;
		$request .= 'Content-length: '.strlen($ping).$r;
		$request .= 'User-Agent: TYPO3/'.$version.$r;
		$request .= 'Connection: close'.$r.$r;
		$request .= $ping;

		// Open socket
		$errno  = 0;
		$errstr = 0;
		if($this->timeout) {
			$fp = fsockopen($bits['host'], $bits['port'], $errno, $errstr, $this->timeout);
		} else {
			$fp = fsockopen($bits['host'], $bits['port'], $errno, $errstr);
		}

		if(!$fp) {
			return array(false, 'Could not connect to '.$target.'.');
		}

		//send trackback
		fputs($fp, $request);

		//get results
		$headers        = '';
		$contents       = '';
		$gotFirstLine   = false;
		$gettingHeaders = true;

		while(!feof($fp)) {
			$line = fgets($fp, 4096);
			if (!$gotFirstLine) {
				// Check line for '200'
				if (strstr($line, '200') === false) {
					$result = array(
						'success' => false, 
						'message' => 'HTTP status code was not 200'
					);
					
					return $result;
				}
				$gotFirstLine = true;
			}
			if (trim($line) == '') {
				$gettingHeaders = false;
			}
			if (!$gettingHeaders) {
				$contents .= trim($line);
			} else {
				$headers  .= trim($line);	
			}
		}

		fclose($fp);

		//did the ping succeed?
		$matches = array();
		if(preg_match('/<error>.*0.*<\/error>/s', $contents)) {
			$result = array(
				'success' => true,
				'message' => ''
			);
		} elseif(preg_match('/<message>(.*)<\/message>/s', $contents, $matches)) {
			$result = array(
				'success' => false, 
				'message' => $matches[1]
			);
		} else {
			$result = array(
				'success' => false, 
				'message' => 'This might not be a Trackback URL!'.$contents
			);
		}

		return $result;
	}

	/**
	 * Produces the XML response for trackbackers with success/error message.
	 *
	 * @param	boolean		$success
	 * @param	string		$err_response
	 * @return	boolean
	 */
	function sendResponse($success = false, $err_response = '')
	{
		// Default error response in case of problems...
		if (!$success && empty($err_response)) {
			$err_response = 'An error occured while trying to log your trackback...';
		}
		// Start response to trackbacker...
		$r = "\n";
		$response  = '<?xml version="1.0" encoding="'.$this->encoding.'"?>'.$r;
		$response .= '<response>'.$r;
		// Send back response...
		if ($success) {
			// Trackback received successfully...
			$response .= "\t".'<error>0</error>'.$r;
		} else {
			// Something went wrong...
			$response .= "\t".'<error>1</error>'.$r;
			$response .= "\t".'<message>'.$this->xmlSafe($err_response).'</message>'.$r;
		}
		// End response to trackbacker
		$response .= '</response>';

		return $response;
	}

	/**
	 * Produces embedded RDF representing metadata for the post,
	 * allowing clients to auto-discover the TrackBack Ping URL.
	 *
	 * @param	string		$permalink
	 * @param	string		$trackbackURL
	 * @return	string
	 */
	function getEmbeddedRdf($permalink, $trackbackURL) {
		$RFC822_date = date('r', $this->post['datetime']);
		$title       = $this->post['title'];
		$excerpt     = $this->getExcerpt();
		$author      = $this->post['author'];

		$r = "\n";
		$rdf  = '<!-- '.$r;
		$rdf .= '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" '.$r;
		$rdf .= '	xmlns:dc="http://purl.org/dc/elements/1.1/" '.$r;
		$rdf .= '	xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">'.$r;
		$rdf .= '<rdf:Description'.$r;
		$rdf .= '	rdf:about="'.$this->xmlSafe($permalink).'"'.$r;
		$rdf .= '	dc:identifier="'.$this->xmlSafe($permalink).'"'.$r;
		$rdf .= '	trackback:ping="'.$this->xmlSafe($trackbackURL).'"'.$r;
		$rdf .= '	dc:title="'.$this->xmlSafe($title).'"'.$r;
		$rdf .= '	dc:subject="TrackBack"'.$r;
		$rdf .= '	dc:description="'.$this->xmlSafe($excerpt).'"'.$r;
		$rdf .= '	dc:creator="'.$this->xmlSafe($author).'"'.$r;
		$rdf .= '	dc:date="'.$RFC822_date.'" />'.$r;
		$rdf .= '</rdf:RDF>'.$r;
		$rdf .= '-->'.$r;

		return $rdf;
	}

	/**
	 * Search content for links, and search found links for trackback URLs.
	 *
	 * @param	string		content to parse for trackback links
	 * @return	array		Trackback URLs.
	 */
	function discoverTrackbacks($content) {
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
			if ($link_content = t3lib_div::getURL($link)) { //ToDo wtweb haut daneben weil muss post sein
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


	/***********************************************
	 *
	 * Supporting methods
	 *
	 **********************************************/

	/**
	 * builds the source URL for the trackback - the URL where the original author
	 * can find our post
	 *
	 * @return	string
	 */
	function getSourceURL() {
		//FIXME respect tt_news useHRDate settings
		
		$urlParameters = array(
			'tx_ttnews[year]'    => date('Y', $this->post['datetime']),
			'tx_ttnews[month]'   => date('m', $this->post['datetime']),
			'tx_ttnews[day]'     => date('d', $this->post['datetime']),
			'tx_ttnews[tt_news]' => $this->post['uid']
		);

 		$link = $this->cObj->getTypoLink_URL($this->conf['blogPid'], $urlParameters);
		return t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link;
	}

	/**
	 * builds the URL where trackbacks can be send to. This is used in the rdf
	 * description of the post
	 *
	 * @return	string
	 */
	function getTrackbackURL() {
		$urlParameters = array(
			'tx_ttnews[tt_news]' => $this->post['uid'],
			'type'               => 200,
			'tx_timtab_pi2[trackback]' => 1
		);

		$link = $this->cObj->getTypoLink_URL($this->conf['blogPid'], $urlParameters);
		return t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link;
	}

	/**
	 * builds the link where trackbacks can be send to. This could be used to
	 * show the link on the website
	 *
	 * @return	string
	 */
	function getTrackbackLink() {
		$urlParameters = array(
			'tx_ttnews[tt_news]' => $this->post['uid'],
			'type'               => 200,
			'tx_timtab_pi2[trackback]' => 1
		);

		$link = $this->cObj->getTypoLink('trackback', $this->conf['blogPid'], $urlParameters);
		return $link;
	}

	/**
	 * creates the permaLink URL, alias for getSourceURL
	 *
	 * @return	string
	 */
	function getPermalink() {
		return $this->getSourceURL();
	}

	/**
	 * creates a short excerpt of our post for sending it as trackback excerpt
	 *
	 * @return	string		an excerpt of the current post
	 */
	function getExcerpt() {
		$excerpt = '';
		$max_length = 255; //is not limited by spec but we do

		if(!empty($this->post['short'])) {
			$excerpt = $this->post['short'];
	 	} else {
			$excerpt = $this->post['bodytext'];
		}

		$excerpt = str_replace(chr(10), ' ', strip_tags($excerpt));

		if(strlen($excerpt) > $max_length) {
			$excerpt = substr($excerpt, 0, $max_length - 3).'...';
		}

		return $excerpt;
	}

	/**
	 * builds an array of Trackbacks containing url, status and reason if status
	 * is failed
	 *
	 * @param	string		list of Trackbacks
	 * @param	[type]		$status: ...
	 * @return	array		checked and transformed array of trackback URLs enriched with meta information
	 */
	function getTrackbackStatus($tbField) {
		$trackbacks	= array();
		$tbList		= explode(chr(10), $tbField);
		
		foreach($tbList as $tb) {
			$properties = explode('|', $tb);

			if($properties[1] == 1) {
				//ping already sent
				$reason = '';
			} elseif($properties[2]) {
				//might be an existing error message
				$reason = $properties[2];
			} elseif(!empty($properties[0])) {
				//something mysterious happend
				$reason = 'unknown';
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
	 * @param	array		Trackback status array, with URL, status and errormessage
	 * @return	string
	 */
	function setTrackbackStatus($TBstatus) {
		$TBlist = '';

		foreach($TBstatus as $TB) {
			$TBlist .= $TB['url'].'|'.$TB['status'];
			$TBlist .= $TB['reason'] ? '|'.$TB['reason'] : '';
			$TBlist .= chr(10);
		}

		return trim($TBlist);
	}

	/**
	 * converts the given timestamp into a RFC 2822 compliant date
	 *
	 * @param	integer		timestamp to convert
	 * @return	string
	 */
	function getRfc2822Date($tstamp) {
		return date('r', $tstamp);
	}

	/**
	 * Converts a string into an XML-safe string (replaces &, <, >, " and ')
	 *
	 * @param	string		string to make XML-safe
	 * @return	string		XML-safe string
	 */
	function xmlSafe($string)
	{
		return htmlspecialchars($string, ENT_QUOTES);
	}
	
	/**
	 * creates or updates the contents of the trackback field 
	 * in the form of URL|status|error message if any
	 * 
	 * @param 	string		status: either new or update
	 * @param	string		current content of the trackback field
	 * @param	string		bodytext to check for new trckback URLs
	 * @return	string		new content for the trackback field
	 */
	function getNewTrackbackField($status, $oldTbField, $bodytext) {
		$newTbField = $oldTbField;
		$foundUrls  = $this->discoverTrackbacks($bodytext);
				
		if(count($foundUrls)) {
			if($status == 'update') {
				//find new trackback URLs
				$oldTbUrls = t3lib_div::trimExplode(chr(10), $oldTbField, true);

				$tmpUrls = array();
				foreach($oldTbUrls as $TB) {
					$parts     = t3lib_div::trimExplode('|', $TB);
					$tmpUrls[] = $parts[0];
				}
				//extract the new Trackback URLs
				$newTbUrls = array_diff($foundUrls, $tmpUrls);

				$newTbField = '';
				foreach($newTbUrls as $newUrl) {
					$newTbField .= $newUrl.'|0|new'.chr(10);
				}
				$newTbField = trim($oldTbField.chr(10).$newTbField);

			} elseif($status == 'new') {
				$newTbField = '';
				foreach($foundUrls as $newUrl) {
					$newTbField .= $newUrl.'|0|new'.chr(10);
				}
				$newTbField = trim($newTbField);
			}
		}
		
		return $newTbField;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_trackback.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_trackback.php']);
}
?>