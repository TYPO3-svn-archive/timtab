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
 * XML-RPC Server for the TIMTAB extension
 *
 * $Id$
 *
 * @author    Ingo Renner <typo3@ingo-renner.com>
 * @author    Ingo Schommer <me@chillu.com>
 */

$PATH_timtab = t3lib_extMgm::extPath('timtab');
require_once($PATH_timtab.'3rdparty/lib.ixr.php');
require_once($PATH_timtab.'class.tx_timtab_lib.php');
require_once($PATH_timtab.'class.tx_timtab_trackback.php');
require_once($PATH_timtab.'pi2/class.tx_timtab_pi2_xmlrpcauth.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');
require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(PATH_t3lib.'class.t3lib_parsehtml_proc.php');

class tx_timtab_pi2_xmlrpcServer extends IXR_Server {
	var $conf;
	var $xmlrpcUser;
	var $status;

	function tx_timtab_pi2_xmlrpcServer(&$pObj) {
		$this->conf = $pObj->conf;
		$this->pObj = $pObj;
		$this->cObj = $pObj->cObj; // needed for sending trackback pings

		// Blogger API
		$blggr = array();
		if($this->conf['enableBlogger']) {
			$blggr = array(
				'blogger.newPost'        => 'this:blggrNewPost',
				'blogger.editPost'       => 'this:blggrEditPost',
				'blogger.deletePost'     => 'this:blggrDeletePost',
				'blogger.getRecentPosts' => 'this:blggrGetRecentPosts',
				'blogger.getUserInfo'	 => 'this:blggrGetUserInfo',
				'blogger.getUsersBlogs'  => 'this:blggrGetUsersBlogs',
			);
		} else {
			// always needed
			$blggr = array(
				'blogger.getUsersBlogs'  => 'this:blggrGetUsersBlogs',
			);
		}

		// MetaWeblog API
		$mw = array();
		if($this->conf['enableMetaWeblog']) {
			$mw = array(
				'metaWeblog.newPost'        => 'this:mwNewPost',
				'metaWeblog.editPost'       => 'this:mwEditPost',
				'metaWeblog.getPost'        => 'this:mwGetPost',
				'metaWeblog.getCategories'  => 'this:mwGetCategories',
				'metaWeblog.getRecentPosts' => 'this:mwGetRecentPosts',
				'metaWeblog.newMediaObject' => 'this:mwNewMediaObject',
				// MetaWeblog API aliases for Blogger API
				// see http://www.xmlrpc.com/stories/storyReader$2460
				'metaWeblog.deletePost'     => 'this:blggrDeletePost',
				'metaWeblog.getUsersBlogs'  => 'this:blggrGetUsersBlogs',
			);
		}

		// Movable Type API
		$mt = array();
		if($this->conf['enableMovableType']) {
			$mt = array(
			//FIXME implement Movable Type API
			/* planed, but nothing implemented yet			 
				'mt.getCategoryList'      => 'this:mtGetCategoryList',
				'mt.getRecentPostTitles'  => 'this:mtGetRecentPostTitles',
				'mt.getPostCategories'    => 'this:mtGetPostCategories',
				'mt.setPostCategories'    => 'this:mtSetPostCategories',
				'mt.supportedMethods'     => 'this:mtSupportedMethods',
				'mt.supportedTextFilters' => 'this:mtSupportedTextFilters',
				'mt.getTrackbackPings'    => 'this:mtGetTrackbackPings',
				'mt.publishPost'          => 'this:mtPublishPost',
			*/
			);
		}

		// PingBack
		$pb = array(
			'pingback.ping'                    => 'this:pbPing',
			'pingback.extensions.getPingbacks' => 'this:pbGetPingbacks',
		);

		// nothing really usefull
		$demo = array(
			'demo.sayHello'      => 'this:demoSayHello',
			'demo.addTwoNumbers' => 'this:demoAddTwoNumbers',
		);

		$this->IXR_Server( array_merge($blggr, $mw, $mt, $pb, $demo) );
	}


	/***********************************************
	 *
	 * Blogger
	 *
	 **********************************************/

	/**
	 * creates a new post and optionally publishes it using the blogger API
	 *
	 * @param	array		array of arguments: [0]appKey, [1]blogId, [2]username, [3]password
	 * @return	string		the posts id
	 */
	function blggrNewPost($args) {
		$this->escape($args);
		$appKey   = $args[0]; //unused
		$blogId   = $args[1]; //unused
		$username = $args[2];
		$password = $args[3];
		$content  = $args[4];
		$publish  = (int) !$args[5];
		$this->status = 'new';

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		//TODO use helper functions to get title and category
		$title      = $this->getBlggrTitle($content);
		$categories = $this->getBlggrCategory($content);
		$content    = $this->cleanBlggrPost($content);

		$time = time();
		$insertFields = array(
			'pid'      => $this->conf['pidStorePosts'],
			'hidden'   => $publish,
			'title'    => $title,
			'bodytext' => $this->transformContent('db', $content),
			'author'   => $username,
			'tstamp'   => $time,
			'crdate'   => $time,
			'datetime' => $time,
			'type'     => 3,
		);

		//processing of trackbacks
		$tb = t3lib_div::makeInstance('tx_timtab_trackback');
		$insertFields['tx_timtab_trackbacks'] = $tb->getNewTrackbackField(
			$this->status, 
			'',
			$insertFields['bodytext']
		);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news', $insertFields);
		$insertFields['uid'] = $insertId = $GLOBALS['TYPO3_DB']->sql_insert_id();

		if(!$insertId) {
			return new IXR_Error(500, 'Sorry, your entry could not be posted. Something wrong happened.');
		}

		//processing of trackbacks
		$tb->initSend($this->conf, $insertFields);
		$tb->sendPings();

		$this->setPostCategories($insertId, $categories);

		//TODO handle pingbacks

		tx_timtab_lib::clearPageCache($this->conf['clearPageCacheOnUpdate']);

		return strval($insertId);
	}

	/**
	 * edits an existing post and optionally publishes it using the blogger API
	 *
	 * @param	array		array of arguments: [0]appKey, [1]postId, [2]username, [3]password, [4]numberOfPosts, [5]content, [6]publish
	 * @return	boolean
	 */
	function blggrEditPost($args) {
		$this->escape($args);
		$appKey   = $args[0]; //unused
		$postId   = $args[1];
		$username = $args[2];
		$password = $args[3];
		$content  = $args[4];
		$publish  = (int) !$args[5];
		$this->status = 'update';

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$title      = $this->getBlggrTitle($content);
		$categories = $this->getBlggrCategory($content);
		$content    = $this->cleanBlggrPost($content);

		$updateFields = array(
			'hidden'   => $publish,
			'title'    => addslashes($title),
			'bodytext' => $this->transformContent('db', $content),
			'author'   => '', //$username, //let's see what we can do with the author field
			'tstamp'   => time(),
		);

		//processing of trackbacks
		$tb = t3lib_div::makeInstance('tx_timtab_trackback');
		$updateFields['tx_timtab_trackbacks'] = $tb->getNewTrackbackField(
			$this->status, 
			$this->getOldTrackbackField($postId),
			$updateFields['bodytext']
		);
		
		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tt_news',
			'uid = '. (int) $postId,
			$updateFields
		);

		if(!$res) {
			return new IXR_Error(500, 'Internal Server Error. Couldn\'t connect to database.');
		}

		$this->setPostCategories($postId, $categories);

		//processing of trackbacks
		$tb->initSend($this->conf, $updateFields);
		$tb->sendPings();

		//TODO handle pingbacks

		tx_timtab_lib::clearPageCache($this->conf['clearPageCacheOnUpdate']);

		return true;
	}

	/**
	 * deletes a post from the server (actually marks it deleted) using the blogger API
	 *
	 * @param	array		array of arguments: [0]appKey, [1]postId, [2]username, [3]password
	 * @return	boolean
	 */
	function blggrDeletePost($args) {
		$this->escape($args);
		$appKey   = $args[0]; //unused
		$postId   = $args[1];
		$username = $args[2];
		$password = $args[3];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tt_news',
			'uid = '. (int) $postId,
			array('deleted' => 1)
		);

		if(!$res) {
			return new IXR_Error(500, 'Internal Server Error. Couldn\'t connect to database.');
		}

		return '';
	}

	/**
	 * undocumented but existing blogger method
	 * gets the last $numPost posts
	 *
	 * @param	array		array of arguments: [0]appKey, [1]blogId, [2]username, [3]password, [4]numberOfPosts
	 * @return	array		$numPosts last posts
	 */
	function blggrGetRecentPosts($args) {
		$this->escape($args);
		$appKey   = $args[0]; //unused
		$blogId   = $args[1]; //unused
		$username = $args[2];
		$password = $args[3];
		$numPosts = $args[4];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, datetime, title, bodytext, category',
			'tt_news',
			'pid = '.$this->conf['pidStorePosts'].' AND type = 3 AND deleted = 0',
			'',
			'datetime DESC',
			(int) $numPosts
		);

		if(!$res) {
			return new IXR_Error(500, 'Database connection brocken or query failed.');;
		}

		if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
			while($post = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$catArray   = $this->getPostCategories($post['uid']);
				$categories = implode(', ', $catArray);

				$content  = '<title>'.$post['title'].'</title>';
				$content .= '<category>'.$categories.'</category>';
				$content .= $this->transformContent('rte', $post['bodytext']);

				$struct[] = array(
					'userid'      => 0, //??? post author uid
					'dateCreated' => new IXR_Date($post['datetime']),
					'content'     => $content,
					'postid'      => $post['uid'],
				);
			}

			//we need the reverse order of the DB result
			$recentPosts = array();
			foreach($struct as $post) {
				$recentPosts[] = $post;
			}

			return $recentPosts;
		} else {
			return new IXR_Error(100, 'No Posts available');
		}

	}

	/**
	 * retrieves a list of weblogs for which a user has posting privileges
	 * using the blogger API
	 *
	 * @param	array		array of arguments: [0]appKey, [1]username, [2]password
	 * @return	array
	 */
	function blggrGetUsersBlogs($args) {
		$this->escape($args);
		$appKey   = $args[0]; //unused
		$username = $args[1];
		$password = $args[2];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$struct = array(
			'url'      => $this->conf['homepage'],
			'blogid'   => '1', //hardcoded, no multiple blogs supported yet
			'blogName' => $this->conf['title']
		);

		return array($struct);
	}

	/**
	 * retrieves information about a blog author using the blogger API
	 *
	 * @param	array		array of arguments: [0]appKey, [1]username, [2]password
	 * @return	struct
	 */
	function blggrGetUserInfo($args) {
		$this->escape($args);
		$appKey   = $args[0]; //unused
		$username = $args[1];
		$password = $args[2];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$struct = array(
			'userid'    => '',
			'firstname' => '',
			'lastname'  => '',
			'nickname'  => '',
			'email'     => '',
			'url'       => ''
		);

		return $struct;
	}


	/***********************************************
	 *
	 * MetaWeblog
	 *
	 **********************************************/

	/**
	 * creates a new post using the metaWeblog API
	 *
	 * @param	array		containing the following: [0]blogId, [1]username, [2]password, [3]content, [4]publish
	 * @return	string		representation of the post id
	 */
	function mwNewPost($args) {
		$this->escape($args);
		$blogId   = $args[0]; //unused
		$username = $args[1];
		$password = $args[2];
		$content  = $args[3];
		$publish  = (int) !$args[4];
		$this->status = 'new';

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$time = time();
		$insertFields = array(
			'pid'      => $this->conf['pidStorePosts'],
			'hidden'   => $publish,
			'title'    => $content['title'],
			'bodytext' => $this->transformContent('db', $content['description']),
			'author'   => $username,
			'tstamp'   => $time,
			'crdate'   => $time,
			'datetime' => $time,
			'type'     => 3,
		);

		//processing of trackbacks
		$tb = t3lib_div::makeInstance('tx_timtab_trackback');
		$insertFields['tx_timtab_trackbacks'] = $tb->getNewTrackbackField(
			$this->status, 
			'',
			$insertFields['bodytext']
		);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news', $insertFields);
		$insertFields['uid'] = $insertId = $GLOBALS['TYPO3_DB']->sql_insert_id();

		if(!$insertId) {
			return new IXR_Error(500, 'Sorry, your entry could not be posted. Something wrong happened.');
		}

		//processing of trackbacks
		$tb->initSend($this->conf, $insertFields);
		$tb->sendPings();

		$this->setPostCategories($insertId, $args[3]['categories']);

		//TODO handle pingbacks

		tx_timtab_lib::clearPageCache($this->conf['clearPageCacheOnUpdate']);

		return strval($insertId);
	}

	/**
	 * edit a post with the given ID using the metaWeblog API
	 *
	 * @param	array		array of arguments: [0]postId, [1]username, [2]password, [3]content, [4]publish
	 * @return	boolean
	 */
	function mwEditPost($args) {
		$this->escape($args);
		$postId     = $args[0];
		$username   = $args[1];
		$password   = $args[2];
		$content    = $args[3]; //struct
		$publish    = (int) !$args[4];
		$this->status = 'update';

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$updateFields = array(
			'hidden'   => $publish,
			'title'    => addslashes($content['title']),
			'bodytext' => $this->transformContent('db', $content['description']),
			'author'   => '', //$username, //let's see what we can do with the author field
			'tstamp'   => time(),
		);

		//processing of trackbacks
		$tb = t3lib_div::makeInstance('tx_timtab_trackback');
		$updateFields['tx_timtab_trackbacks'] = $tb->getNewTrackbackField(
			$this->status, 
			$this->getOldTrackbackField($postId),
			$updateFields['bodytext']
		);

		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tt_news',
			'uid = ' . (int) $postId,
			$updateFields
		);

		if(!$res) {
			return new IXR_Error(500, 'Internal Server Error. Couldn\'t connect to database.');
		}

		$this->setPostCategories($postId, $args[3]['categories']);

		//processing of trackbacks
		$tb->initSend($this->conf, $updateFields);
		$tb->sendPings();

		//TODO handle pingbacks

		tx_timtab_lib::clearPageCache($this->conf['clearPageCacheOnUpdate']);

		return true;
	}

	/**
	 * gets a specific post using the metaWeblog API
	 *
	 * @param	array		array of arguments: [0]postId, [1]username, [2]password
	 * @return	struct
	 */
	function mwGetPost($args) {
		$this->escape($args);
		$postId   = $args[0];
		$username = $args[1];
		$password = $args[2];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		// FIXME don't use magic numbers
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, datetime, title, bodytext, category',
			'tt_news',
			'uid = '. (int) $postId.' AND type = 3 AND deleted = 0'
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
				'description' => $this->transformContent('rte', $post['bodytext']),
				'title'       => $post['title'],
				'link'        => '', //unused
				'permalink'   => '', //unused
				'categories'  => $this->getPostCategories($post['uid']),
			);

			return $struct;
		} else {
			return new IXR_Error(404, 'Sorry, no such post.');
		}
	}

	/**
	 * gets the systems categories using the metaWeblog API
	 *
	 * @param	array		array of arguments: [0]blogId, [1]username, [2]password
	 * @return	struct
	 */
	function mwGetCategories($args) {
		$this->escape($args);
		$blogId   = $args[0]; //unused
		$username = $args[1];
		$password = $args[2];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		#$cObj = t3lib_div::makeInstance('tslib_cObj');

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
			$struct = array(
				'categoryId'   => $row['uid'],
				'description'  => $row['description'],
				'categoryName' => $row['title'],
				'htmlUrl'      => '',
				'rssUrl'       => '',
			);

			/*
			$struct['htmlUrl'] = $pageURL . $cObj->getTypoLink_URL($pid,array('tx_ttnews[cat]'=>$row['uid']));
			$struct['rssUrl'] = $pageURL . $cObj->getTypoLink_URL($pid,array('tx_ttnews[cat]'=>$row['uid'], 'type'=>100));
			*/

			// API says it is a struct, but everybody is implementing it as an array
			if($this->conf['strictAPI'] == 1) {
				$categories_struct[$row['title']] = $struct;
			} else {
				$categories_struct[] = $struct;
			}
		}

		return $categories_struct;
	}

	/**
	 * gets the last n posts using the metaWeblog API
	 *
	 * @param	array		array of arguments: [0]blogId, [1]username, [2]password, [3]numberOfPosts
	 * @return	array
	 */
	function mwGetRecentPosts($args) {
		$this->escape($args);
		$blogId   = $args[0]; //unused
		$username = $args[1];
		$password = $args[2];
		$numPosts = $args[3];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, datetime, title, bodytext, category',
			'tt_news',
			'pid = '.$this->conf['pidStorePosts'].' AND type = 3 AND deleted = 0',
			'',
			'datetime DESC',
			(int) $numPosts
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
					'description' => $this->transformContent('rte', $post['bodytext']),
					'title'       => $post['title'],
					'link'        => '', //unused
					'permalink'   => '', //unused
					'categories'  => $this->getPostCategories($post['uid']),		      	);
			}

			//we need the reverse order of the DB result
			$recentPosts = array();
			foreach($struct as $post) {
				$recentPosts[] = $post;
			}

			return $recentPosts;
		} else {
			return new IXR_Error(100, 'No Posts available');
		}
	}

	/**
	 * creates a new file using the metaWeblog API
	 *
	 * @param	array		array of arguments: [0]postId, [1]username, [2]password, [3]fileContent
	 * @return	struct
	 */
	function mwNewMediaObject($args) {
		$this->escape($args);
		$postId   = $args[0];
		$username = $args[1];
		$password = $args[2];
		$data     = $args[3];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		if(!$this->conf['enableUploads']) {
			return new IXR_Error(405, 'No uploads allowed for this site.');
		}

		if( t3lib_div::validPathStr($data['name']) ) {
			$filename = t3lib_div::getFileAbsFileName( $GLOBALS['TYPO3_CONF_VARS']['BE']['RTE_imageStorageDir'].substr($data['name'],1) );
		} else {
			return new IXR_Error(100, 'Invalid Filename.');
		}

		if(t3lib_div::verifyFilenameAgainstDenyPattern($filename) != true) {
			return new IXR_Error(100, 'Filetype is not allowed.');
		} elseif(t3lib_div::writeFile($filename, $data['bits']) != true) {
			return new IXR_Error(500, 'File could not be written.');
		} else {
			return array('url' => t3lib_div::getIndpEnv('TYPO3_SITE_URL').$filename);
		}
	}

	/***********************************************
	 *
	 * Pingback
	 *
	 **********************************************/

	/**
	 * [Describe function...]
	 *
	 * @param	array		$args: ...
	 * @return	[type]		...
	 */
	function pbPing($args) {
		$this->escape($args);

	}

	/**
	 * [Describe function...]
	 *
	 * @param	array		$args: ...
	 * @return	[type]		...
	 */
	function pbGetPingbacks($args) {
		$this->escape($args);

	}


	/***********************************************
	 *
	 * Demo
	 *
	 **********************************************/

	/**
	 * say hello
	 *
	 * @param	array		$args: ...
	 * @return	string
	 */
	function demoSayHello($args) {
		return 'Hello!';
	}

	/**
	 * add two numbers
	 *
	 * @param	array		$args: ...
	 * @return	number
	 */
	function demoAddTwoNumbers($args) {
		$this->escape($args);
		$number1 = $args[0];
		$number2 = $args[1];

		return $number1 + $number2;
	}


	/***********************************************
	 *
	 * non webservices // supporting methods
	 *
	 **********************************************/

	/**
	 * returns a numeric array of categories asigned to the given post
	 *
	 * @param	integer		the post ID to get the categories for
	 * @return	array		array of categories belonging to the given post
	 */
	function getPostCategories($postId) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tt_news_cat.uid, tt_news_cat.title',
			'tt_news',
			'tt_news_cat_mm',
			'tt_news_cat',
			'AND tt_news.uid = '. (int) $postId
		);

		$tempCategories = array();
		while($cat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tempCategories[] =	$cat;
		}

		$categories = array();
		unset($cat);
		foreach($tempCategories as $cat) {
			$categories[] = (string) $cat['title'];
		}

		return $categories;
	}

	/**
	 * sets the categories for a post - since we are working in the FE scope we
	 * cannot use TCE which would handle this automaticly.
	 *
	 * @param	integer		the post ID to set the categories for
	 * @param	array		an array of category to assign to the post
	 * @return	void
	 */
	function setPostCategories($postId, $catsXmlrpc) {
		if(count($catsXmlrpc) > 0) {
			$where = implode('\', \'', $catsXmlrpc);
			$where = 'AND title IN (\''.$where.'\')';

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'tt_news_cat',
				'deleted = 0 '.$where
			);

			$catsNew = array();
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$catsNew[] = $row['uid'];
			}
			unset($row, $res, $where);

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid_foreign',
				'tt_news_cat_mm',
				'uid_local = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($postId, 'tt_news_cat_mm')
			);

			$catsOld = array();
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$catsOld[] = $row['uid_foreign'];
			}
			unset($row, $res);

			$addCats = array_diff($catsNew, $catsOld);
			$rmvCats = array_diff($catsOld, $catsNew);

			//add new categories
			foreach($addCats as $cat) {
				$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'tt_news_cat_mm',
					array(
						'uid_local'   => $postId,
						'uid_foreign' => $cat,
						'sorting'     => 1
					)
				);
			}
			unset($cat);

			//remove categories which are not assigned to the post anymore
			foreach($rmvCats as $cat) {
				$GLOBALS['TYPO3_DB']->exec_DELETEquery(
					'tt_news_cat_mm',
					'uid_local = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($postId, 'tt_news_cat_mm').
						' AND uid_foreign = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($cat, 'tt_news_cat_mm')
				);
			}
		}
	}

	/**
	 * authenticates a BE user by using auth services
	 *
	 * @param	string		username the username
	 * @param	string		password the users password in clear text
	 * @return	boolean
	 */
	function authUser($username, $password) {
		//init
		$auth = t3lib_div::makeInstance('tx_timtab_pi2_xmlrpcAuth');
		$auth->initAuth($username, $password);

		//get user
		if(!$this->xmlrpcUser = $auth->getUser()) {
			return false;
		}

		//auth user
		$accessOK = $auth->authUser();
		#$authOK   = $this->xmlrpcUser->check('tables_modify', 'tt_news');

		$authOK   = true;	//TODO $this->xmlrpcUser needs to be an object to check table permissions

		#$isObj    = is_object($this->xmlrpcUser); //false, but needs to be true

		return $accessOK && $authOK;
	}

	/**
	 * Performs transformation of content to/from Client. The argument $dirRTE determines the direction.
	 * This function is called in two situations:
	 * a) Right before content from database is sent to the Client it needs transformation
	 * b) When content is sent from the Client into the database it needs transformation back again
	 *
	 * @param	string		Keyword: "rte" means direction from DB to client, "db" means direction from client to DB
	 * @param	string		text to transform.
	 * @return	string		transformed content
	 */
	function transformContent($dirRTE, $content) {
		global $TCA;

		$table      = 'tt_news';
		$field      = 'bodytext';
		$pid        = $this->conf['pidStorePosts'];
		$RTErelPath = '';

		if($dirRTE == 'db') {
			$content = stripslashes($content);
		}

		//start getting $specConf --- taken from t3lib_BEfunc::getTCAtypes()
		$specConf = array();
		$_EXTKEY = $this->pObj->extKey;
		include($GLOBALS['PATH_timtab'].'ext_tables.php');
		$fieldList = explode(',', $TCA[$table]['types']['3']['showitem']);

		foreach($fieldList as $k => $v)	{
			list($pFieldName, $pAltTitle, $pPalette, $pSpec) = t3lib_div::trimExplode(';', $v);
			if($pFieldName == $field) {
				$defaultExtras = is_array($TCA[$table]['columns'][$field]) ? $TCA[$table]['columns'][$field]['defaultExtras'] : '';
				$specConf      = t3lib_BEfunc::getSpecConfParts($pSpec, $defaultExtras);
				break;
			}
		}
		//end getting $specConf

		$pageTSconfig = t3lib_BEfunc::getPagesTSconfig($this->conf['pidStorePosts']);
		$thisConfig   = $pageTSconfig['RTE.']['default.'];

		if ($specConf['rte_transform'])	{
			$p = t3lib_BEfunc::getSpecConfParametersFromArray($specConf['rte_transform']['parameters']);
			if ($p['mode'])	{	// There must be a mode set for transformation

					// Initialize transformation:
				$parseHTML = t3lib_div::makeInstance('t3lib_parsehtml_proc');
				$parseHTML->init($table.':'.$field, $pid);
				$parseHTML->setRelPath($RTErelPath);

					// Perform transformation:
				$content = $parseHTML->RTE_transform($content, $specConf, $dirRTE, $thisConfig);
			}
		}

		return $content;
	}

	/**
	 * escapes an array of key => value pairs
	 *
	 * taken from wordpress, thanks!
	 *
	 * @param	array		array of key => value pairs to escape
	 * @return	void
	 */
	function escape(&$array) {
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$this->escape($array[$k]);
			} else if (is_object($v)) {
				// skip
			} else {
				$array[$k] = addslashes($v);
			}
		}
	}

	/**
	 * as the blogger API doesn't support title directly we'll get it from the
	 * post content surrounded by a <title> tag
	 *
	 * original taken from wordpress, thanks!
	 *
	 * @param	string		the posts content
	 * @return	string		the posts title if found, a default title otherwise
	 */
	function getBlggrTitle($content) {
		$matchTitle = array();
		$title      = $this->conf['bloggerTitle'];

		if (preg_match('/<title>(.+?)<\/title>/is', $content, $matchTitle)) {
			$title = $matchTitle[0];

			//TODO replace with substr()
			$title = preg_replace('/<title>/si', '', $title);
			$title = preg_replace('/<\/title>/si', '', $title);
		}

		return $title;
	}

	/**
	 * the blogger API doesn't support categories, but if we find a <category>
	 * tag in the content of the post we will use the categories found enclosed
	 * by it
	 *
	 * original taken from wordpress, thanks!
	 *
	 * @param	string		the posts content
	 * @return	array		array of category names
	 */
	function getBlggrCategory($content) {
		$matchCat = $category = array();

		if (preg_match('/<category>(.+?)<\/category>/is', $content, $matchCat)) {
			$category = trim($matchCat[1], ',');
			$category = explode(',', $category);
		}

		return $category;
	}

	/**
	 * cleans a blogger Post from possible <title> and <category> tags
	 *
	 * @param	string		the blogger post
	 * @return	string		the blogger post without <title> and <category> tags
	 */
	function cleanBlggrPost($content) {
		$content = preg_replace('/<title>.+?<\/title>/si', '', $content);
		$content = preg_replace('/<category>.+?<\/category>/si', '', $content);

		return trim($content);
	}
	
	/**
	 * 
	 */
	function getOldTrackbackField($id) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_timtab_trackbacks',
			'tt_news',
			'uid = '.intval($id)
		);		
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		return $row['tx_timtab_trackbacks'];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2_xmlrpcserver.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2_xmlrpcserver.php']);
}

?>