<?php

########################################################################
# Extension Manager/Repository config file for ext: "timtab"
# 
# Auto generated 15-11-2005 15:56
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'TIMTAB Weblog',
	'description' => 'TYPO3 Is More Than A Blog (but now offers blog functionality, too) - Weblog for TYPO3',
	'category' => 'plugin',
	'shy' => '',
	'dependencies' => 'cms,sv,css_styled_content,tt_news,ve_guestbook',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'TYPO3_version' => '4.2.0-4.3.99',
	'PHP_version' => '4.1.0-6.0.0',
	'module' => '',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => '',
	'createDirs' => '',
	'modify_tables' => 'tt_news,ve_guestbook',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Ingo Renner, Lina Wolf',
	'author_email' => 'typo3@ingo-renner.com, 2010@lotypo3.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'private' => 0,
	'download_password' => '',
	'version' => '0.6.0-dev',	// Don't modify this! Managed automatically during upload to repository.
	'_md5_values_when_last_written' => 'a:135:{s:9:"ChangeLog";s:4:"87b6";s:20:"class.ext_update.php";s:4:"f6f6";s:22:"class.tx_timtab_be.php";s:4:"f81e";s:27:"class.tx_timtab_catmenu.php";s:4:"f5e2";s:22:"class.tx_timtab_fe.php";s:4:"018f";s:23:"class.tx_timtab_lib.php";s:4:"9c80";s:28:"class.tx_timtab_pingback.php";s:4:"e50d";s:29:"class.tx_timtab_trackback.php";s:4:"1a9d";s:12:"ext_icon.gif";s:4:"90a5";s:17:"ext_localconf.php";s:4:"21ad";s:14:"ext_tables.php";s:4:"a08e";s:14:"ext_tables.sql";s:4:"d8b3";s:28:"ext_typoscript_constants.txt";s:4:"7faa";s:24:"ext_typoscript_setup.txt";s:4:"dfbb";s:27:"icon_tx_timtab_blogroll.gif";s:4:"7d43";s:23:"icon_tx_timtab_post.gif";s:4:"afd3";s:11:"lib.ixr.php";s:4:"82b3";s:13:"locallang.php";s:4:"f1db";s:16:"locallang_db.php";s:4:"59ce";s:7:"tca.php";s:4:"d003";s:11:"CVS/Entries";s:4:"823a";s:17:"CVS/Entries.Extra";s:4:"0871";s:21:"CVS/Entries.Extra.Old";s:4:"0172";s:15:"CVS/Entries.Old";s:4:"682c";s:14:"CVS/Repository";s:4:"5cd3";s:8:"CVS/Root";s:4:"b82f";s:14:"doc/manual.sxw";s:4:"6550";s:15:"doc/CVS/Entries";s:4:"7adb";s:21:"doc/CVS/Entries.Extra";s:4:"fd6d";s:25:"doc/CVS/Entries.Extra.Old";s:4:"fd6d";s:19:"doc/CVS/Entries.Old";s:4:"fd4d";s:18:"doc/CVS/Repository";s:4:"0f2e";s:12:"doc/CVS/Root";s:4:"b82f";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:27:"pi1/class.tx_timtab_pi1.php";s:4:"818f";s:35:"pi1/class.tx_timtab_pi1_wizicon.php";s:4:"d7b4";s:17:"pi1/locallang.php";s:4:"1585";s:15:"pi1/CVS/Entries";s:4:"dd84";s:21:"pi1/CVS/Entries.Extra";s:4:"acfa";s:25:"pi1/CVS/Entries.Extra.Old";s:4:"acfa";s:19:"pi1/CVS/Entries.Old";s:4:"a898";s:18:"pi1/CVS/Repository";s:4:"6cb9";s:12:"pi1/CVS/Root";s:4:"b82f";s:24:"pi1/static/editorcfg.txt";s:4:"8401";s:22:"pi1/static/CVS/Entries";s:4:"129d";s:28:"pi1/static/CVS/Entries.Extra";s:4:"7cdd";s:32:"pi1/static/CVS/Entries.Extra.Old";s:4:"7cdd";s:26:"pi1/static/CVS/Entries.Old";s:4:"272f";s:25:"pi1/static/CVS/Repository";s:4:"3487";s:19:"pi1/static/CVS/Root";s:4:"b82f";s:27:"pi2/class.tx_timtab_pi2.php";s:4:"7dd1";s:38:"pi2/class.tx_timtab_pi2_xmlrpcauth.php";s:4:"62c6";s:40:"pi2/class.tx_timtab_pi2_xmlrpcserver.php";s:4:"b5fc";s:15:"pi2/CVS/Entries";s:4:"9395";s:21:"pi2/CVS/Entries.Extra";s:4:"a71c";s:25:"pi2/CVS/Entries.Extra.Old";s:4:"7102";s:19:"pi2/CVS/Entries.Old";s:4:"f491";s:18:"pi2/CVS/Repository";s:4:"620c";s:12:"pi2/CVS/Root";s:4:"b82f";s:14:"pi3/ce_wiz.gif";s:4:"02b6";s:27:"pi3/class.tx_timtab_pi3.php";s:4:"f176";s:35:"pi3/class.tx_timtab_pi3_wizicon.php";s:4:"f4a0";s:17:"pi3/locallang.php";s:4:"4fc3";s:29:"res/realurl_example_setup.php";s:4:"7d2a";s:25:"res/buttons/atom_news.gif";s:4:"5dec";s:29:"res/buttons/firefox_80x15.png";s:4:"deb3";s:24:"res/buttons/rdf_news.gif";s:4:"5271";s:24:"res/buttons/rss_news.gif";s:4:"2b53";s:29:"res/buttons/timtab_button.gif";s:4:"9189";s:35:"res/buttons/typo3_button_logo_2.gif";s:4:"0fc4";s:23:"res/buttons/xfn-btn.gif";s:4:"bc51";s:23:"res/buttons/CVS/Entries";s:4:"ffdb";s:29:"res/buttons/CVS/Entries.Extra";s:4:"0d6d";s:33:"res/buttons/CVS/Entries.Extra.Old";s:4:"dfef";s:27:"res/buttons/CVS/Entries.Old";s:4:"962a";s:26:"res/buttons/CVS/Repository";s:4:"2559";s:20:"res/buttons/CVS/Root";s:4:"b82f";s:15:"res/CVS/Entries";s:4:"e7d8";s:21:"res/CVS/Entries.Extra";s:4:"d492";s:25:"res/CVS/Entries.Extra.Old";s:4:"d492";s:19:"res/CVS/Entries.Old";s:4:"85e2";s:18:"res/CVS/Repository";s:4:"d406";s:12:"res/CVS/Root";s:4:"b82f";s:29:"res/kubrick/kubrick_main.tmpl";s:4:"31ed";s:31:"res/kubrick/kubrick_single.tmpl";s:4:"2266";s:28:"res/kubrick/kubrick_std.tmpl";s:4:"e8da";s:32:"res/kubrick/kubrick_tt_news.tmpl";s:4:"67b8";s:37:"res/kubrick/kubrick_ve_guestbook.tmpl";s:4:"044a";s:21:"res/kubrick/style.css";s:4:"e244";s:23:"res/kubrick/CVS/Entries";s:4:"8bf9";s:29:"res/kubrick/CVS/Entries.Extra";s:4:"da8a";s:33:"res/kubrick/CVS/Entries.Extra.Old";s:4:"da8a";s:27:"res/kubrick/CVS/Entries.Old";s:4:"989c";s:26:"res/kubrick/CVS/Repository";s:4:"d220";s:20:"res/kubrick/CVS/Root";s:4:"b82f";s:32:"res/kubrick/images/kubrickbg.jpg";s:4:"fb89";s:37:"res/kubrick/images/kubrickbgcolor.jpg";s:4:"59ca";s:36:"res/kubrick/images/kubrickbgwide.jpg";s:4:"49ea";s:36:"res/kubrick/images/kubrickfooter.jpg";s:4:"b921";s:36:"res/kubrick/images/kubrickheader.jpg";s:4:"7627";s:30:"res/kubrick/images/CVS/Entries";s:4:"9736";s:36:"res/kubrick/images/CVS/Entries.Extra";s:4:"8e60";s:40:"res/kubrick/images/CVS/Entries.Extra.Old";s:4:"a455";s:34:"res/kubrick/images/CVS/Entries.Old";s:4:"1186";s:33:"res/kubrick/images/CVS/Repository";s:4:"f1b5";s:27:"res/kubrick/images/CVS/Root";s:4:"b82f";s:43:"res/patches/class.t3lib_parsehtml.php.patch";s:4:"251b";s:23:"res/patches/CVS/Entries";s:4:"53a9";s:29:"res/patches/CVS/Entries.Extra";s:4:"f5a5";s:33:"res/patches/CVS/Entries.Extra.Old";s:4:"f5a5";s:27:"res/patches/CVS/Entries.Old";s:4:"cd4b";s:26:"res/patches/CVS/Repository";s:4:"37b2";s:20:"res/patches/CVS/Root";s:4:"b82f";s:18:"static/CVS/Entries";s:4:"7915";s:24:"static/CVS/Entries.Extra";s:4:"c1d1";s:28:"static/CVS/Entries.Extra.Old";s:4:"619c";s:22:"static/CVS/Entries.Old";s:4:"eef6";s:21:"static/CVS/Repository";s:4:"d797";s:15:"static/CVS/Root";s:4:"b82f";s:33:"static/kubrick_main/constants.txt";s:4:"ecb0";s:29:"static/kubrick_main/setup.txt";s:4:"abe8";s:31:"static/kubrick_main/CVS/Entries";s:4:"0a14";s:37:"static/kubrick_main/CVS/Entries.Extra";s:4:"9fde";s:41:"static/kubrick_main/CVS/Entries.Extra.Old";s:4:"9fde";s:35:"static/kubrick_main/CVS/Entries.Old";s:4:"6b99";s:34:"static/kubrick_main/CVS/Repository";s:4:"07c2";s:28:"static/kubrick_main/CVS/Root";s:4:"b82f";s:31:"static/webservice/constants.txt";s:4:"80d1";s:27:"static/webservice/setup.txt";s:4:"53a7";s:29:"static/webservice/CVS/Entries";s:4:"63d2";s:35:"static/webservice/CVS/Entries.Extra";s:4:"9fde";s:39:"static/webservice/CVS/Entries.Extra.Old";s:4:"9fde";s:33:"static/webservice/CVS/Entries.Old";s:4:"19f0";s:32:"static/webservice/CVS/Repository";s:4:"0abd";s:26:"static/webservice/CVS/Root";s:4:"b82f";}',
);

?>