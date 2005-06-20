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
 * $Id$
 *
 * @author    Ingo Renner <typo3@ingo-renner.com>
 * @author    Ingo Schommer <me@chillu.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   81: class tx_timtab_pi2_xmlrpcServer extends IXR_Server
 *   85:     function tx_timtab_pi2_xmlrpcServer(&$pObj)
 *
 *              SECTION: MetaWeblog
 *  155:     function mwNewPost($args)
 *  207:     function mwEditPost($args)
 *  251:     function mwGetPost($args)
 *  295:     function mwGetCategories($args)
 *  351:     function mwGetRecentPosts($args)
 *  405:     function mwNewMediaObject($args)
 *
 *              SECTION: Blogger
 *  442:     function blggrNewPost($args)
 *  459:     function blggrEditPost($args)
 *  479:     function blggrDeletePost($args)
 *  509:     function blggrGetUsersBlogs($args)
 *  533:     function blggrGetUserInfo($args)
 *
 *              SECTION: Pingback
 *  566:     function pbPing($args)
 *  578:     function pbGetPingbacks($args)
 *
 *              SECTION: Demo
 *  594:     function demoSayHello($args)
 *  604:     function demoAddTwoNumbers($args)
 *
 *              SECTION: non webservices
 *  623:     function getPostCategories($postId)
 *  654:     function authUser($username, $password)
 *  683:     function transformContent($dirRTE, $value)
 *
 * TOTAL FUNCTIONS: 19
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


$PATH_timtab = t3lib_extMgm::extPath('timtab');
require_once($PATH_timtab.'lib.ixr.php');
require_once($PATH_timtab.'pi2/class.tx_timtab_pi2_xmlrpcauth.php');
require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(PATH_t3lib.'class.t3lib_parsehtml_proc.php');

class tx_timtab_pi2_xmlrpcServer extends IXR_Server {
	var $conf;
	var $xmlrpcUser;

	function tx_timtab_pi2_xmlrpcServer(&$pObj) {
		$this->conf = $pObj->conf;
		$this->pObj = $pObj;

		// Blogger API
		$blggr = array();
		if($this->conf['enableBlogger']) {
			$blggr = array(
				'blogger.newPost'       => 'this:blggrNewPost',
				'blogger.editPost'      => 'this:blggrEditPost',
				'blogger.deletePost'    => 'this:blggrDeletePost',
				'blogger.getUsersBlogs' => 'this:blggrGetUsersBlogs',
				'blogger.getUserInfo'	=> 'this:blggrGetUserInfo',
			);
		} else {
			//always needed
			$blggr = array(
				'blogger.getUsersBlogs' => 'this:blggrGetUsersBlogs',
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
			);
		}

		// Movable Type API
		$mt = array();
		if($this->conf['enableMovableType']) {
			$mt = array();	//nothing yet
		}

		// PingBack
		$pb = array(
			'pingback.ping'                    => 'this:pbPing',
			'pingback.extensions.getPingbacks' => 'this:pbGetPingbacks',
		);

		//nothing really usefull
		$demo = array(
			'demo.sayHello'      => 'this:demoSayHello',
			'demo.addTwoNumbers' => 'this:demoAddTwoNumbers',
		);

		$this->IXR_Server( array_merge($blggr, $mw, $mt, $pb, $demo) );
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
		$blogId   = $args[0]; //unused
		$username = $args[1];
		$password = $args[2];
		$content  = $args[3];
		$publish  = (int) !$args[4];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

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
		$insertFields = array(
			'pid'      => $this->conf['pidStore'],
			'hidden'   => $publish,
			'title'    => $content['title'],
			'bodytext' => $this->transformContent('db', $content['description']),
			'author'   => $username,
			'tstamp'   => $time,
			'crdate'   => $time,
			'datetime' => $time,
			'type'     => 3,
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news', $insertFields);
		$insertID = $GLOBALS['TYPO3_DB']->sql_insert_id();

		if(!$insertID) {
			return new IXR_Error(500, 'Sorry, your entry could not be posted. Something wrong happened.');
		}

		//TODO add relations to categories
		//TODO handle pingbacks and trackbacks
		return strval($insertID);
	}

	/**
	 * edit a post with the given ID using the metaWeblog API
	 *
	 * @param	array		array of arguments: [0]postId, [1]username, [2]password, [3]content, [4]publish
	 * @return	boolean
	 */
	function mwEditPost($args) {
		$postId     = $args[0];
		$username   = $args[1];
		$password   = $args[2];
		$content    = $args[3]; //struct
		$publish    = (int) !$args[4];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$time = time();
		$updateFields = array(
			'hidden'    => $publish,
			'title'     => addslashes($content['title']),
			'bodytext'  => $this->transformContent('db', $content['description']),
			'author'    => '', //$username, //let's see what we can do with the author field
			'tstamp'    => $time,
			'datetime'  => $content['dateCreated']->getTimestamp(),
			//'starttime' => $time, //???
		);

		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tt_news',
			'uid = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($postId, 'tt_news'),
			$updateFields
		);

		//TODO clear cache for blog page

		if(!$res) {
			return new IXR_Error(500, 'Internal Server Error. Couldn\'t connect to database.');
		}

		//TODO handle new pingbacks	and trackbacks
		return true;
	}

	/**
	 * gets a specific post using the metaWeblog API
	 *
	 * @param	array		array of arguments: [0]postId, [1]username, [2]password
	 * @return	struct
	 */
	function mwGetPost($args) {
		$postId   = $args[0];
		$username = $args[1];
		$password = $args[2];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, datetime, title, bodytext, category',
			'tt_news',
			'uid = '.$postId.' AND type = 3 AND deleted = 0'
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

			//??? API says it shall be a struct, but everybody is implementing it as array
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
			'pid = '.$this->conf['pidStore'].' AND type = 3 AND deleted = 0',
			'',
			'datetime DESC',
			$numPosts
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

			//we need reverse order of DB result
			$recent_posts = array();
			for ($i = 0; $i < count($struct); $i++) {
				array_push($recent_posts, $struct[$i]);
			}

			return $recent_posts;
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
		$postid      = $args[0];
		$username    = $args[1];
		$password    = $args[2];
		$fileContent = $args[3]['bits'];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		if( t3lib_div::validPathStr($args[3]['name']) ) {
			$filename = t3lib_div::getFileAbsFileName( $GLOBALS['TYPO3_CONF_VARS']['BE']['RTE_imageStorageDir'].substr($args[3]['name'],1) );
		} else {
			return new IXR_Error(100, 'Invalid Filename.');
		}

		if( t3lib_div::verifyFilenameAgainstDenyPattern($filename) != true ) {
			return new IXR_Error(100, 'Filetype is not allowed.');
		} elseif(t3lib_div::writeFile($filename, $fileContent) != 1) {
			return new IXR_Error(100, 'Filetype could not be written.');
		} else {
			return array('url' => t3lib_div::getIndpEnv("REMOTE_ADDR").substr($params[3]["name"],1) );
		}
	}

	/***********************************************
	 *
	 * Blogger
	 *
	 **********************************************/

	/**
	 * creates a new post and optionally publishes it using the blogger API
	 *
	 * @param	array		array of arguments: [0]postId, [1]blogId, [2]username, [3]password
	 * @return	string
	 */
	function blggrNewPost($args) {
		$appKey   = $args[0]; //unused
		$blogId   = $args[1];
		$username = $args[2];
		$password = $args[3];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}
	}

	/**
	 * edits an existing post and optionally publishes it using the blogger API
	 *
	 * @param	array		array of arguments: [0]appKey, [1]postId, [2]username, [3]password, [4]numberOfPosts, [5]content, [6]publish
	 * @return	boolean
	 */
	function blggrEditPost($args) {
		$appKey   = $args[0]; //unused
		$postId   = $args[1];
		$username = $args[2];
		$password = $args[3];
		$numPosts = $args[4];
		$content  = $args[5];
		$publish  = (int) !$args[6];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}
	}

	/**
	 * deletes a post from the server (actually marks it deleted) using the blogger API
	 *
	 * @param	array		array of arguments: [0]appKey, [1]postId, [2]username, [3]password
	 * @return	boolean
	 */
	function blggrDeletePost($args) {
		$appKey   = $args[0]; //unused
		$postId   = $args[1];
		$username = $args[2];
		$password = $args[3];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tt_news',
			'uid = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($postId, 'tt_news'),
			array('deleted' => 1)
		);

		if(!$res) {
			return new IXR_Error(500, 'Internal Server Error. Couldn\'t connect to database.');
		}

		return '';
	}

	/**
	 * retrieves a list of weblogs for which a user has posting privileges
	 * using the blogger API
	 *
	 * @param	array		array of arguments: [0]appKey, [1]username, [2]password
	 * @return	array
	 */
	function blggrGetUsersBlogs($args) {
		$appKey   = $args[0]; //unused
		$username = $args[1];
		$password = $args[2];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$struct = array(
			'url'      => $this->conf['homepage'],
			'blogid'   => '1', //hardcoded, no multiple blogs supported
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
		$appKey   = $args[0]; //unused
		$username = $args[1];
		$password = $args[2];

		if(!$this->authUser($username, $password)) {
			return new IXR_Error(403, 'Not authorized: Bad username/password combination.');
		}

		$struct = array(
			'userid' => '',
			'firstname' => '',
			'lastname' => '',
			'nickname' => '',
			'email' => '',
			'url' => ''
		);

		return $struct;
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
		//taken from wordpress


	}

	/**
	 * [Describe function...]
	 *
	 * @param	array		$args: ...
	 * @return	[type]		...
	 */
	function pbGetPingbacks($args) {

	}

	/***********************************************
	 *
	 * Demo
	 *
	 **********************************************/

	/**
	 * [Describe function...]
	 *
	 * @param	array		$args: ...
	 * @return	[type]		...
	 */
	function demoSayHello($args) {
		return 'Hello!';
	}

	/**
	 * [Describe function...]
	 *
	 * @param	array		$args: ...
	 * @return	[type]		...
	 */
	function demoAddTwoNumbers($args) {
		$number1 = $args[0];
		$number2 = $args[1];

		return $number1 + $number2;
	}

	/***********************************************
	 *
	 * non webservices
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
			'AND tt_news.uid = '.$postId
		);
		#debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);

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
		$isObj    = is_object($this->xmlrpcUser); //false

		return $accessOK && $authOK;
	}

	/**
	 * Performs transformation of content to/from Client. The argument $dirRTE determines the direction.
	 * This function is called in two situations:
	 * a) Right before content from database is sent to the Client it needs transformation
	 * b) When content is sent from the Client into the database it needs transformation back again
	 *
	 * @param	string		Keyword: "rte" means direction from DB to client, "db" means direction from client to DB
	 * @param	string		Value to transform.
	 * @return	string		transformed content
	 */
	function transformContent($dirRTE, $value) {
		global $TCA;

		$table      = 'tt_news';
		$field      = 'bodytext';
		$pid        = $this->conf['pidStore'];
		$RTErelPath = '';

		//start getting $specConf --- taken from t3lib_BEfunc::getTCAtypes()
		$specConf = array();
		$_EXTKEY = $this->pObj->extKey;
		include($GLOBALS['PATH_timtab'].'ext_tables.php');
		$fieldList = explode(',', $TCA[$table]['types']['3']['showitem']); // 0 should be 3!?

		foreach($fieldList as $k => $v)	{
			list($pFieldName, $pAltTitle, $pPalette, $pSpec) = t3lib_div::trimExplode(';', $v);
			if($pFieldName == $field) {
				$defaultExtras = is_array($TCA[$table]['columns'][$field]) ? $TCA[$table]['columns'][$field]['defaultExtras'] : '';
				$specConf      = t3lib_BEfunc::getSpecConfParts($pSpec, $defaultExtras);
				break;
			}
		}
		//end getting $specConf

		$pageTSconfig = t3lib_BEfunc::getPagesTSconfig($this->conf['pidStore']);
		$thisConfig   = $pageTSconfig['RTE.']['default.'];

		if ($specConf['rte_transform'])	{
			$p = t3lib_BEfunc::getSpecConfParametersFromArray($specConf['rte_transform']['parameters']);
			if ($p['mode'])	{	// There must be a mode set for transformation

					// Initialize transformation:
				$parseHTML = t3lib_div::makeInstance('t3lib_parsehtml_proc');
				$parseHTML->init($table.':'.$field, $pid);
				$parseHTML->setRelPath($RTErelPath);

					// Perform transformation:
				$value = $parseHTML->RTE_transform($value, $specConf, $dirRTE, $thisConfig);
			}
		}

		return $value;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2_xmlrpcserver.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2_xmlrpcserver.php']);
}

?>
