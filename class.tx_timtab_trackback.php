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
 * Trackback class for the timtab extension, the majority of the code
 * is taken from PHP TrackBack, see: http://phptrackback.sourceforge.net
 *
 * $Id$
 *
 * @author	Ingo Renner <typo3@ingo-renner.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   43: class tx_timtabb_trackback
 *
 * TOTAL FUNCTIONS: 0
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_timtab_trackback {
	var $prefixId = 'tx_timtab_trackback';		// Same as class name
	var $scriptRelPath = 'class.tx_timtab_trackback.php';	// Path to this script relative to the extension dir.
	var $extKey = 'timtab';	// The extension key.

	var $conf;
	var $pObj;

	var $encoding;
	var $timeout;
	var $blogName;
	var $tt_news;

	/**
	 * initialization of this class
	 * 
	 * @param	object	the parent object with our configuration
	 * @param	array	the tt_ews record - or parts of it - which is being processed
	 * @return	void
	 */
	function init($pObj, $tt_news) {
		$this->conf = $pObj->conf;
		$this->pObj = $pObj;
		
		$this->encoding = 'UTF-8';
		$this->timeout  = $this->conf['connectionTimeout'];
		$this->blogName = $this->conf['title'];
		$this->tt_news  = $tt_news; 
	}

	/**
	 * Sends a trackback ping to a specified trackback URL.
	 * allowing clients to auto-discover the TrackBack Ping URL.
	 * 
	 * @param string trackback target 
	 * @param string post title 
	 * @param string post excerpt 
	 * @return boolean 
	 */
	function ping($target)
	{
		$response = '';
		$result   = array(); 
		$source   = $this->buildSourceURL();
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
		$ping .= '&title='.rawurlencode($this->tt_news['title']);
		$ping .= '&blog_name='.rawurlencode($this->blogName);
		$ping .= '&excerpt='.rawurlencode($excerpt);

		$r = "\r\n";
		$request  = 'POST '.$bits['path'].$bits['query'].' HTTP/1.1'.$r;
		$request .= 'Host: '.$bits['host'].$r;
		$request .= 'Content-type: application/x-www-form-urlencoded'.$r;
		$request .= 'Content-length: '.strlen($ping).$r;
		$request .= 'User-Agent: TYPO3 - get.content.right'.$r;
		$request .= 'Connection: close'.$r.$r;
		$request .= $ping;
		
		// Open socket
		$errno = 0;
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
		$contents       = '';
		$gotFirstLine   = false;
		$gettingHeaders = true;
		
		while(!feof($fp)) {
			$line = fgets($fp, 4096);
			if (!$gotFirstLine) {
				// Check line for '200'
				if (strstr($line, '200') === false) {
					return array(false, 'HTTP status code was not 200');
				}
				$gotFirstLine = true;
			}
			if (trim($line) == '') {
				$gettingHeaders = false;
			}
			if (!$gettingHeaders) {
				$contents .= trim($line);
			}	
		}
		
		fclose($fp);
		
		// Did the ping succeed?
		if(strpos($contents, '<error>0</error>')) {
			$result = array(true, '');
		} elseif(strpos($contents, '<error>1</error>')) {
			$start = strpos($contents, '<message>') + 9;
			$end   = strpos($contents, '</message>');
			
			$result = array(false, substr($contents, $start, $end - $start));
		} else {
			$result = array(false, trim($contents));
		}

		return $result;
	}
	
	/**
	 * Produces XML response for trackbackers with success/error message.
	 * 
	 * <code><?php
	 * // Set page header to XML
	 * header('Content-Type: text/xml'); // MUST be the 1st line
	 * //
	 * // Instantiate the class
	 * //
	 * include('trackback_cls.php');
	 * $trackback = new Trackback('BLOGish', 'Ran Aroussi', 'UTF-8');
	 * //
	 * // Get trackback information
	 * //
	 * $tb_id = $trackback->post_id; // The id of the item being trackbacked
	 * $tb_url = $trackback->url; // The URL from which we got the trackback
	 * $tb_title = $trackback->title; // Subject/title send by trackback
	 * $tb_expert = $trackback->expert; // Short text send by trackback
	 * //  
	 * // Do whatever to log the trackback (save in DB, flatfile, etc...)
	 * //
	 * if (TRACKBACK_LOGGED_SUCCESSFULLY) {
	 * 	// Logged successfully...
	 * 	echo $trackback->recieve(true);
	 * } else {
	 * 	// Something went wrong...
	 * 	echo $trackback->recieve(false, 'Explain why you return error');
	 * }
	 * ?></code>
	 * 
	 * @param boolean $success 
	 * @param string $err_response 
	 * @return boolean 
	 */
	function recieve($success = false, $err_response = '')
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
			$response .= '<error>0</error>'.$r;
		} else {
			// Something went wrong...
			$response .= '<error>1</error>'.$r;
			$response .= '<message>'.$this->xmlSafe($err_response).'</message>'.$r;
		} 
		// End response to trackbacker
		$response .= '</response>';

		return $response;
	}

	/**
	 * Produces embedded RDF representing metadata for the post,
	 * allowing clients to auto-discover the TrackBack Ping URL.
	 * 
	 * @param string $RFC822_date 
	 * @param string $title 
	 * @param string $expert 
	 * @param string $permalink 
	 * @param string $trackback 
	 * @param string $author 
	 * @return string 
	 */
	function getEmbeddedRdf($RFC822_date, $title, $excerpt, $permalink, $trackback, $author = '')
	{
		if (!$author || empty($author)) {
			$author = $this->author;
		} 

		$r = "\n";
		$rdf  = '<!-- '.$r;
		$rdf .= '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'.$r;
		$rdf .= '	xmlns:dc="http://purl.org/dc/elements/1.1/"'.$r;
		$rdf .= '	xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/"">'.$r;
		$rdf .= '<rdf:Description'.$r;
		$rdf .= '	rdf:about="'.$this->xmlSafe($permalink).'"'.$r;
		$rdf .= '	dc:identifier="'.$this->xmlSafe($permalink).'"'.$r;
		$rdf .= '	trackback:ping="'.$this->xmlSafe($trackback).'"'.$r;
		$rdf .= '	dc:title="'.$this->xmlSafe($title).'"'.$r;
		$rdf .= '	dc:subject="TrackBack"'.$r;
		$rdf .= '	dc:description="'.$this->xmlSafe($excerpt).'"'.$r;
		$rdf .= '	dc:creator='.$this->xmlSafe($author).'"'.$r;
		$rdf .= '	dc:date="'.$RFC822_date.'" />'.$r;
		$rdf .= '</rdf:RDF>'.$r;
		$rdf .= '-->'.$r;

		return $rdf;
	}

	/**
	 * Search content for links, and search found links for trackback URLs.
	 * 
	 * @param string content to parse for trackback links 
	 * @return array Trackback URLs.
	 */
	function tbAutoDiscovery($content)
	{ 
		// Get a list of UNIQUE links from text...
		#$reg_exp = '/(http)+(s)?:(\\/\\/)((\\w|\\.)+)(\\/)?(\\S+)?/i';
		$reg_exp = '/(http|https)(:\/\/)([^\s<>]+)/i';
		
		// Make sure each link ends with [space]
		$content = eregi_replace('www.', 'http://www.', $content);
		$content = eregi_replace('http://http://', 'http://', $content);
		$content = eregi_replace('"', ' "', $content);
		$content = eregi_replace('\'', ' \'', $content);
		$content = eregi_replace('>', ' >', $content); 
		
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
			if ($link_content = implode('', file($link))) {
				$link_rdf = array();
				preg_match_all('/(<rdf:RDF.*?<\/rdf:RDF>)/sm', $link_content, $link_rdf, PREG_SET_ORDER);

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
	 * builds the source URL for thetrackback - the URL where the original author
	 * can find our post
	 * 
	 * @param integer the tt_news uid we are building the URL for
	 * @return string
	 */
	function buildSourceURL() {
		$urlParameters = array(
			'tx_ttnews[year]'    => date('Y', $this->tt_news['datetime']),
			'tx_ttnews[month]'   => date('m', $this->tt_news['datetime']),
			'tx_ttnews[day]'     => date('d', $this->tt_news['datetime']),
			'tx_ttnews[tt_news]' => $this->tt_news['uid']
		);
 		
		return t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->pObj->cObj->getTypoLink_URL($this->conf['blogPid'], $urlParameters);
	}
	
	/**
	 * creates a short excerpt of our post for sending it as trackback excerpt
	 * 
	 * @return string an excerpt of the current post
	 */
	function getExcerpt() {
		$excerpt = '';
		$max_length = 255; //is not limited by spec but we do
	 	
		if(!empty($this->tt_news['short'])) {
			$excerpt = $this->tt_news['short'];
	 	} else {
			$excerpt = $this->tt_news['bodytext'];
		}
	 	
		if(strlen($excerpt) > $max_length) {
			$excerpt = substr($excerpt, 0, $max_length - 3).'...';
		}
	 	
		return $excerpt;
	}
	 
	/**
	 * converts the given timestamp into a RFC 2822 compliant date
	 * 
	 * @param integer timestamp to convert
	 * @return string
	 */
	function getRfc2822Date($timestamp) {
		return date('r', $timestamp);
	}

	/**
	 * Converts a string into an XML-safe string (replaces &, <, >, " and ')
	 * 
	 * @param string $string 
	 * @return string 
	 */
	function xmlSafe($string)
	{
		return htmlspecialchars($string, ENT_QUOTES);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_trackback.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_trackback.php']);
}

?>
