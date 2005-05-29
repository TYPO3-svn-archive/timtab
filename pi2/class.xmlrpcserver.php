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
include_once(PATH_t3lib.'class.t3lib_userauth.php');
require_once(PATH_tslib.'class.tslib_content.php');
#include_once(PATH_t3li
#include_once(PATH_t3lib.'class.t3lib.'class.t3lib_userAuthGroup.php');
#include_once(PATH_t3lib.'class.t3lib_beUserAuth.php');

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
			'blogger.newPost'		=> 'this:blggrNewPost',
			'blogger.editPost'		=> 'this:blggrEditPost',
			'blogger.deletePost'    => 'this:blggrDeletePost',
			'blogger.getUsersBlogs' => 'this:blggrGetUsersBlogs',
			'blogger.getUserInfo'	=> 'this:blggrGetUserInfo',			
						
			// PingBack
			'pingback.ping'                    => 'this:pbPing',
			'pingback.extensions.getPingbacks' => 'this:pbGetPingbacks',
			
			//nothing really usefull
			'demo.sayHello'      => 'this:demoSayHello',
			'demo.addTwoNumbers' => 'this:demoAddTwoNumbers',
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
		$content  = $args[3];
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
		$insertArray = array(
			'pid'      => $this->conf['pidStore'],
			'hidden'   => $publish,
			'title'    => addslashes($content['title']),
			'bodytext' => addslashes($content['description']), //TODO content->DB transformation
			'author'   => $username,
			'tstamp'   => $time,
			'crdate'   => $time,
			'datetime' => $time,
			'type'     => 3,	
		);		
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news', $insertArray);
		$insertID = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		if(!$insertID) {
			return new IXR_Error(500, 'Sorry, your entry could not be posted. Something wrong happened.');
		} 
		
		//TODO add relations to categories	
		//TODO handle trackbacks
		return strval($insertID);		
	}
	
	/**
	 * edits the post with the given id
	 * 
	 * @param args array of arguments: [0]postid, [1]username, [2]password, [3]struct, [4]publish
	 * @return bool
	 */
	function mwEditPost($args) {
		$postid     = $args[0];
		$username   = addslashes($args[1]);
		$password   = addslashes($args[2]);
		$content    = $args[3]; //struct		
		$publish    = intval(!$args[4]);
	
		//TODO authenticate user
	
		$time = time();
		$updateArray = array(
			'hidden'    => $publish,
			'title'     => addslashes($content['title']),
			'bodytext'  => addslashes($content['description']),//TODO content->DB transformation
			//'author'    => $username, //let's see what we can do with the author field
			'tstamp'    => $time,
			'datetime'  => $content['dateCreated']->getTimestamp(),
			//'starttime' => $time //???
		);
		
		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tt_news', 
			'uid = '.$postid, 
			$updateArray
		); 	
	
		if(!$res) {
			return new IXR_Error(500, 'Internal Server Error. Couldn\'t connect to database.'); 
		} 
			
		//TODO handle new pingbacks		
		return true;
	}
	
	/**
	 * gets a specific post
	 * 
	 * @param args array of arguments: [0]postid, [1]username, [2]password
	 * @retrun struct
	 */
	function mwGetPost($args) {
		$postid   = intval($args[0]);
		$username = addslashes($args[1]);
		$password = addslashes($args[2]);
	
		//TODO authenticate user here
	
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, datetime, title, bodytext, category',
			'tt_news',
			'WHERE uid = '.$postid.' AND type = 3 AND deleted = 0'
		);	
	
		if(!$res) {
			return new IXR_error(500, 'Internal Server Error. Couldn\'t connect to database.'); 
		} 
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
			$post   = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$struct = array(
				'dateCreated' => new IXR_Date($post['datetime']),
				'userid'      => 0, //??? post author uid
				'postid'      => $post['uid'],
				'description' => $post['bodytext'],
				'title'       => $post['title'],
			/*	
				'link' => '',
				'permalink' => '',
				'categories' => '',
				'mt_excerpt' => '',
	      		'mt_text_more' => '',
	      		'mt_allow_comments' => '',
	      		'mt_allow_pings' => '',
	      	*/
			);
						
			return $struct;
		} else {
			return new IXR_Error(404, 'Sorry, no such post.');
		}
	}
	
	function mwGetCategories($args) {	
		//$blogId = intval($args[0]);
		$username = addslashes($args[1]);
		$password = addslashes($args[2]);		
		
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		
		#$pageURL = (strrpos($conf['pid'],'/')==strlen($conf['pid']))? $conf['pageURL'] : $conf['pageURL'].'/';
		#$pid = $conf['pid'];
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, title, description',
			'tt_news_cat',
			'deleted = 0'
		);
		
		if(!$res) {
			return new IXR_Error(500, 'Internal Server Error. Couldn\'t connect to database.');
		}
		
		$categories_struct = array();			
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$struct = array();
			$struct['categoryId']   = $row['uid'];
			$struct['description']  = $row['description'];
			$struct['categoryName'] = $row['title'];
			$struct['htmlUrl']      = '';
			$struct['rssUrl']       = '';
			
			/*
			$struct['htmlUrl'] = $pageURL . $cObj->getTypoLink_URL($pid,array('tx_ttnews[cat]'=>$row['uid']));
			$struct['rssUrl'] = $pageURL . $cObj->getTypoLink_URL($pid,array('tx_ttnews[cat]'=>$row['uid'], 'type'=>100));
			$struct['title'] = $row['title'];
			*/
			
			//??? API says it shall be a struct, but everybody is implementing it as array
			if($this->conf['strictAPI'] == 1) {
				$categories_struct[$row['title']] = $struct;
			} else {
				$categories_struct[] = $struct;
			}
		}
		
		return $categories_struct;		
	}
	
	function mwGetRecentPosts($args) {		
		$username = addslashes($args[1]);
		$password = addslashes($args[2]);
		$numposts = intval($args[3]);
		
		//TODO authenticate user here
	
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, datetime, title, bodytext, category',
			'tt_news',
			'pid = '.$this->conf['pidStore'].' AND type = 3 AND deleted = 0',
			'',
			'datetime DESC',
			$numposts
		);
	
		if(!$res) {
			return new IXR_Error(500, 'Database connection brocken or query failed.');;
		} 
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
			while($post = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$struct[] = array(
					'dateCreated' => new IXR_Date($post['datetime']),
					'userid'      => 0, //??? post author uid
					'postid'      => $post['uid'],
					'description' => $post['bodytext'], //TODO DB->content tansformation
					'title'       => $post['title'],
				/*	
					'link' => '',
					'permalink' => '',
					'categories' => '',
					'mt_excerpt' => '',
		      		'mt_text_more' => '',
		      		'mt_allow_comments' => '',
		      		'mt_allow_pings' => '',
		      	*/
		      	);
			}
		
			$recent_posts = array();
			for ($i = 0; $i < count($struct); $i++) {
				array_push($recent_posts, $struct[$i]);
			}
	  
			return $recent_posts;
		} else {
			return new IXR_Error(100, 'No Posts available');
		}
	}
	
	function mwNewMediaObject($args) {
		$postid      = $args[0];
		$username    = addslashes($args[1]);
		$password    = addslashes($args[2]);
		$filecontent = $args[3]['bits'];
		
		if( t3lib_div::validPathStr($args[3]['name']) ) {
			$filename = t3lib_div::getFileAbsFileName( $GLOBALS['TYPO3_CONF_VARS']['BE']['RTE_imageStorageDir'].substr($args[3]['name'],1) );
		} else {
			return new IXR_Error(100, 'Invalid Filename.');
		}
		
		if( t3lib_div::verifyFilenameAgainstDenyPattern($filename) != true ) {
			return new IXR_Error(100, 'Filetype is not allowed.');
		} elseif(t3lib_div::writeFile($filename, $filecontent) != 1) {
			return new IXR_Error(100, 'Filetype could not be written.'); 
		} else {
			return array('url' => t3lib_div::getIndpEnv("REMOTE_ADDR").substr($params[3]["name"],1) );
		}
	}
	
	//Blogger
	function blggrNewPost($args) {
		
	}

	function blggrEditPost($args) {
		
	}

	function blggrDeletePost($args) {
		
	}
	
	function blggrGetUsersBlogs($args) {
		
		$userName = $args[1];
		$userPass = $args[2];
		
		//TODO authenticate user here
				
		$struct = array(
			'isAdmin'  => false, //TODO fill properly
			'url'      => $this->conf['homepage'],
			'blogid'   => '1', //hardcoded, no multiple blogs supported
			'blogName' => $this->conf['title'] 
		);

		return array($struct);		
	}
	
	function blggrGetUserInfo($args) {
		
	}
	
	//Pingback
	function pbPing($args) {
		//taken from wordpress
		
		
	}	
	
	function pbGetPingbacks($args) {
		
	}
	
	//demo
	function demoSayHello($args) {
		return 'Hello!';
	}

	function demoAddTwoNumbers($args) {
		$number1 = $args[0];
		$number2 = $args[1];
		
		return $number1 + $number2;
	}
		
	//non webservices
	function authUser($username, $password) {
		$auth = t3lib_div::makeInstance('t3lib_beUserAuth');
		$auth->formfield_status = 'login';
		$auth->formfield_uname  = $username;
		$auth->formfield_uident = md5($password);
		$auth->security_level   = 'normal';
		
		//now authenticate
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.xmlrpcserver.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.xmlrpcserver.php']);
}

?>
