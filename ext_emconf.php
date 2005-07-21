<?php

########################################################################
# Extension Manager/Repository config file for ext: "timtab"
# 
# Auto generated 21-06-2005 15:59
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'TYPO3 Is More Than A Blog (but now offers blog functionality, too)',
	'description' => 'This is a suite of tools to build a blog using TYPO3 it requires some other extensions',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,sv,css_styled_content,tt_news,ve_guestbook',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'TYPO3_version' => '3.7.0-4.0.0',
	'PHP_version' => '4.1.0-6.0.0',
	'module' => '',
	'state' => 'alpha',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_news',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Ingo Renner',
	'author_email' => 'typo3@ingo-renner.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'private' => 0,
	'download_password' => '',
	'version' => '0.0.1',	// Don't modify this! Managed automatically during upload to repository.
	'_md5_values_when_last_written' => 'a:142:{s:9:"ChangeLog";s:4:"feda";s:19:"class.tx_timtab.php";s:4:"6705";s:22:"class.tx_timtab_be.php";s:4:"1f01";s:28:"class.tx_timtab_pingback.php";s:4:"e50d";s:29:"class.tx_timtab_trackback.php";s:4:"fca8";s:12:"ext_icon.gif";s:4:"90a5";s:17:"ext_localconf.php";s:4:"24ea";s:14:"ext_tables.php";s:4:"e808";s:14:"ext_tables.sql";s:4:"8671";s:28:"ext_typoscript_constants.txt";s:4:"6c53";s:24:"ext_typoscript_setup.txt";s:4:"6ab0";s:27:"icon_tx_timtab_blogroll.gif";s:4:"7d43";s:23:"icon_tx_timtab_post.gif";s:4:"afd3";s:11:"lib.ixr.php";s:4:"cf42";s:15:"lib.ixr_1_6.php";s:4:"dc04";s:13:"locallang.php";s:4:"99f1";s:16:"locallang_db.php";s:4:"3dbe";s:7:"tca.php";s:4:"d003";s:17:"trackback_cls.php";s:4:"ef21";s:11:"CVS/Entries";s:4:"06f7";s:17:"CVS/Entries.Extra";s:4:"4ddd";s:21:"CVS/Entries.Extra.Old";s:4:"e06e";s:15:"CVS/Entries.Old";s:4:"ecd1";s:14:"CVS/Repository";s:4:"5cd3";s:8:"CVS/Root";s:4:"b82f";s:14:"doc/manual.sxw";s:4:"2a82";s:19:"doc/wizard_form.dat";s:4:"4d37";s:20:"doc/wizard_form.html";s:4:"f381";s:15:"doc/CVS/Entries";s:4:"5daa";s:21:"doc/CVS/Entries.Extra";s:4:"39a6";s:25:"doc/CVS/Entries.Extra.Old";s:4:"a6f4";s:19:"doc/CVS/Entries.Old";s:4:"4990";s:18:"doc/CVS/Repository";s:4:"0f2e";s:12:"doc/CVS/Root";s:4:"b82f";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:27:"pi1/class.tx_timtab_pi1.php";s:4:"5967";s:35:"pi1/class.tx_timtab_pi1_wizicon.php";s:4:"662c";s:17:"pi1/locallang.php";s:4:"7e18";s:15:"pi1/CVS/Entries";s:4:"ce43";s:21:"pi1/CVS/Entries.Extra";s:4:"0ae1";s:25:"pi1/CVS/Entries.Extra.Old";s:4:"d2e5";s:19:"pi1/CVS/Entries.Old";s:4:"f9ed";s:18:"pi1/CVS/Repository";s:4:"6cb9";s:12:"pi1/CVS/Root";s:4:"b82f";s:24:"pi1/static/editorcfg.txt";s:4:"8401";s:22:"pi1/static/CVS/Entries";s:4:"129d";s:28:"pi1/static/CVS/Entries.Extra";s:4:"7cdd";s:32:"pi1/static/CVS/Entries.Extra.Old";s:4:"7cdd";s:26:"pi1/static/CVS/Entries.Old";s:4:"272f";s:25:"pi1/static/CVS/Repository";s:4:"3487";s:19:"pi1/static/CVS/Root";s:4:"b82f";s:27:"pi2/class.tx_timtab_pi2.php";s:4:"14e6";s:38:"pi2/class.tx_timtab_pi2_xmlrpcauth.php";s:4:"5bcc";s:40:"pi2/class.tx_timtab_pi2_xmlrpcserver.php";s:4:"beeb";s:15:"pi2/CVS/Entries";s:4:"3976";s:21:"pi2/CVS/Entries.Extra";s:4:"7102";s:25:"pi2/CVS/Entries.Extra.Old";s:4:"7102";s:19:"pi2/CVS/Entries.Old";s:4:"f529";s:18:"pi2/CVS/Repository";s:4:"620c";s:12:"pi2/CVS/Root";s:4:"b82f";s:25:"res/buttons/atom_news.gif";s:4:"5dec";s:19:"res/buttons/css.gif";s:4:"bfce";s:29:"res/buttons/firefox_80x15.png";s:4:"deb3";s:24:"res/buttons/rdf_news.gif";s:4:"5271";s:24:"res/buttons/rss_news.gif";s:4:"2b53";s:26:"res/buttons/somerights.gif";s:4:"a552";s:29:"res/buttons/typo3powered2.gif";s:4:"b721";s:23:"res/buttons/xfn-btn.gif";s:4:"bc51";s:23:"res/buttons/xhtml10.gif";s:4:"8728";s:23:"res/buttons/CVS/Entries";s:4:"fbcc";s:29:"res/buttons/CVS/Entries.Extra";s:4:"f2f7";s:33:"res/buttons/CVS/Entries.Extra.Old";s:4:"f2f7";s:27:"res/buttons/CVS/Entries.Old";s:4:"1760";s:26:"res/buttons/CVS/Repository";s:4:"2559";s:20:"res/buttons/CVS/Root";s:4:"b82f";s:15:"res/CVS/Entries";s:4:"f734";s:21:"res/CVS/Entries.Extra";s:4:"aed0";s:25:"res/CVS/Entries.Extra.Old";s:4:"d41d";s:19:"res/CVS/Entries.Old";s:4:"6cc4";s:18:"res/CVS/Repository";s:4:"d406";s:12:"res/CVS/Root";s:4:"b82f";s:24:"res/kubrick/kubrick.tmpl";s:4:"600f";s:31:"res/kubrick/kubrick_single.tmpl";s:4:"11f3";s:32:"res/kubrick/kubrick_tt_news.tmpl";s:4:"7522";s:37:"res/kubrick/kubrick_ve_guestbook.tmpl";s:4:"89e5";s:21:"res/kubrick/style.css";s:4:"a0bb";s:23:"res/kubrick/CVS/Entries";s:4:"51ec";s:29:"res/kubrick/CVS/Entries.Extra";s:4:"f2a5";s:33:"res/kubrick/CVS/Entries.Extra.Old";s:4:"fe64";s:27:"res/kubrick/CVS/Entries.Old";s:4:"a62c";s:26:"res/kubrick/CVS/Repository";s:4:"d220";s:20:"res/kubrick/CVS/Root";s:4:"b82f";s:32:"res/kubrick/images/kubrickbg.jpg";s:4:"fb89";s:37:"res/kubrick/images/kubrickbgcolor.jpg";s:4:"59ca";s:36:"res/kubrick/images/kubrickbgwide.jpg";s:4:"49ea";s:36:"res/kubrick/images/kubrickfooter.jpg";s:4:"b921";s:36:"res/kubrick/images/kubrickheader.jpg";s:4:"7627";s:30:"res/kubrick/images/CVS/Entries";s:4:"1186";s:36:"res/kubrick/images/CVS/Entries.Extra";s:4:"a455";s:40:"res/kubrick/images/CVS/Entries.Extra.Old";s:4:"a455";s:34:"res/kubrick/images/CVS/Entries.Old";s:4:"3378";s:33:"res/kubrick/images/CVS/Repository";s:4:"f1b5";s:27:"res/kubrick/images/CVS/Root";s:4:"b82f";s:64:"res/patches/class.tslib_pagegen.php.configurableHeadTag.patch_37";s:4:"c5a3";s:69:"res/patches/class.tslib_pagegen.php.configurableHeadTag.patch_38beta1";s:4:"bd6f";s:70:"res/patches/class.tx_veguestbook_pi1.php.extraItemMarkerHook.patch_167";s:4:"5fcf";s:23:"res/patches/CVS/Entries";s:4:"1e91";s:29:"res/patches/CVS/Entries.Extra";s:4:"cb39";s:33:"res/patches/CVS/Entries.Extra.Old";s:4:"cb39";s:27:"res/patches/CVS/Entries.Old";s:4:"3bef";s:26:"res/patches/CVS/Repository";s:4:"37b2";s:20:"res/patches/CVS/Root";s:4:"b82f";s:18:"static/CVS/Entries";s:4:"7915";s:24:"static/CVS/Entries.Extra";s:4:"c1d1";s:28:"static/CVS/Entries.Extra.Old";s:4:"619c";s:22:"static/CVS/Entries.Old";s:4:"eef6";s:21:"static/CVS/Repository";s:4:"d797";s:15:"static/CVS/Root";s:4:"b82f";s:28:"static/kubrick/constants.txt";s:4:"aea5";s:24:"static/kubrick/setup.txt";s:4:"5210";s:26:"static/kubrick/CVS/Entries";s:4:"fd77";s:32:"static/kubrick/CVS/Entries.Extra";s:4:"9fde";s:36:"static/kubrick/CVS/Entries.Extra.Old";s:4:"c557";s:30:"static/kubrick/CVS/Entries.Old";s:4:"de7f";s:29:"static/kubrick/CVS/Repository";s:4:"07c2";s:23:"static/kubrick/CVS/Root";s:4:"b82f";s:35:"static/kubrick_single/constants.txt";s:4:"6f45";s:31:"static/kubrick_single/setup.txt";s:4:"402f";s:33:"static/kubrick_single/CVS/Entries";s:4:"8cac";s:39:"static/kubrick_single/CVS/Entries.Extra";s:4:"9fde";s:43:"static/kubrick_single/CVS/Entries.Extra.Old";s:4:"9fde";s:37:"static/kubrick_single/CVS/Entries.Old";s:4:"05b5";s:36:"static/kubrick_single/CVS/Repository";s:4:"e0bc";s:30:"static/kubrick_single/CVS/Root";s:4:"b82f";s:31:"static/webservice/constants.txt";s:4:"80d1";s:27:"static/webservice/setup.txt";s:4:"301a";s:29:"static/webservice/CVS/Entries";s:4:"384c";s:35:"static/webservice/CVS/Entries.Extra";s:4:"9fde";s:39:"static/webservice/CVS/Entries.Extra.Old";s:4:"9fde";s:33:"static/webservice/CVS/Entries.Old";s:4:"c6a2";s:32:"static/webservice/CVS/Repository";s:4:"0abd";s:26:"static/webservice/CVS/Root";s:4:"b82f";}',
);

?>