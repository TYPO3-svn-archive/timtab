<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Ingo Renner (typo3@ingo-renner.com)
*       and Ingo Schommer (me@chillu.com) 
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
 * XML-RPC Server for the timtab extension 
 *
 * @author    Ingo Renner <typo3@ingo-renner.com>
 * @author    Ingo Schommer <me@chillu.com>
 */
 
require_once(t3lib_extMgm::extPath('timtab').'lib.ixr.php');

class xmlrpcServer extends IXR_Server {
	var $conf;

	function xmlrpcServer($conf) {
		$this->conf = $conf;		
		
		$this->IXR_Server( array(
			//'Server.Status' => 'this:serverStatus',
			
			// MetaWeblog API
			'metaWeblog.newPost'        => 'this:mwNewPost',
			'metaWeblog.editPost'       => 'this:mwEditPost',
			'metaWeblog.getPost'        => 'this:mwGetPost',
			'metaWeblog.getCategories'  => 'this:mwGetCategories',
			'metaWeblog.getRecentPosts' => 'this:mwGetRecentPosts',
			'metaWeblog.newMediaObject' => 'this:mwNewMediaObject',
			// MetaWeblog API aliases for Blogger API
			// see http://www.xmlrpc.com/stories/storyReader$2460
			'metaWeblog.deletePost'     => 'this:blggrDeletePost',
			
			// Blogger API
			'blogger.deletePost'    => 'this:blggrDeletePost',
			'blogger.getUsersBlogs' => 'this:blggrGetUsersBlogs',
			
			// PingBack
			'pingback.ping'                    => 'this:pbPing',
			'pingback.extensions.getPingbacks' => 'this:pbGetPingbacks',
			
			//nothing really usefull
			'demo.sayHello'      => 'this:sayHello',
			'demo.addTwoNumbers' => 'this:addTwoNumbers',
		));	
	}
	
	//MetaWeblog
		
	/**
	 * creates a new post
	 * 
	 * @param args containing the following: [0]blogid, [1]username, [2]password, [3]struct, [4]publish
	 * @return string representation of the post id
	 */
	function mwNewPost($args) {
		//$blogId   = intval($args[0]);
		//TODO auth user
		$username = addslashes($args[1]); 
		$password = addslashes($args[2]);
		$title    = addslashes($args[3]['title']);
		$bodytext = addslashes($args[3]['description']);
		$publish  = intval(!$args[4]);
		
		if(count($args[3]['categories']) > 0) {
			$where = implode('\', \'', $args[3]['categories']);
			$where = 'AND title IN (\''.$where.'\')';			
			
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'tt_news_cat',
				'deleted = 0 '.$where
			);			
		}
		
		$time = time();		
		//TODO check the TYPO3 insert function
		$insertArray = array(
			'pid'      => $this->conf['pidStore'],
			'hidden'   => $publish,
			'title'    => $title,
			'bodytext' => $bodytext,
			'author'   => $username,
			'tstamp'   => $time,
			'crdate'   => $time,
			'datetime' => $time
		);		
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news', $insertArray);
		$insertID = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		if(!$insertID) {
			return new IXR_Error(500, 'Sorry, your entry could not be posted. Something wrong happened.');
		} else {
			//TODO add relations to categories	
		}
	}
	
	/**
	 * edits the post with the given id
	 * 
	 * @param args array of arguments: [0]postid, [1]username, [2]password, [3]struct, [4]publish
	 * @return bool
	 */
	function mwEditPost($args) {
		
	}
	
	/**
	 * gets a specific post
	 * 
	 * @param args array of arguments: [0]postid, [1]username, [2]password
	 * @retrun struct
	 */
	function mwGetPost($args) {
		
	}
	
	function mwGetCategories($args) {
		
	}
	
	function mwGetRecentPosts($args) {
		
	}
	
	function mwNewMediaObject($args) {
		
	}
	
	//Blogger
	function blggrDeletePost($args) {
		
	}
	
	function blggrGetUsersBlogs($args) {
		
	}
	
	//Pingback
	function pbPing($args) {
		
	}	
	
	function pbGetPingbacks($args) {
		
	}
	
	function sayHello($args) {
		return 'Hello!';
	}

	function addTwoNumbers($args) {
		$number1 = $args[0];
		$number2 = $args[1];
		return $number1 + $number2;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.xmlrpcserver.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.xmlrpcserver.php']);
}

?>
