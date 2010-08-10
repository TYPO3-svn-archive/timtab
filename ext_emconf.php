<?php

########################################################################
# Extension Manager/Repository config file for ext "timtab".
#
# Auto generated 18-06-2010 23:03
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TIMTAB Weblog',
	'description' => 'TYPO3 Is More Than A Blog (but now offers blog functionality, too) - Weblog for TYPO3',
	'category' => 'plugin',
	'shy' => '',
	'dependencies' => 'cms,sv,css_styled_content,tt_news,comments',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => '',
	'createDirs' => '',
	'modify_tables' => 'tt_news',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Ingo Renner, Lina Wolf',
	'author_email' => 'typo3@ingo-renner.com, 2010@lotypo3.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.0-dev',
	'_md5_values_when_last_written' => 'a:63:{s:9:"ChangeLog";s:4:"5ddc";s:20:"class.ext_update.php";s:4:"6c97";s:22:"class.tx_timtab_be.php";s:4:"72f3";s:27:"class.tx_timtab_catmenu.php";s:4:"7c5f";s:22:"class.tx_timtab_fe.php";s:4:"0664";s:23:"class.tx_timtab_lib.php";s:4:"6cce";s:28:"class.tx_timtab_pingback.php";s:4:"97e4";s:29:"class.tx_timtab_trackback.php";s:4:"4e58";s:9:"error_log";s:4:"4b82";s:12:"ext_icon.gif";s:4:"90a5";s:17:"ext_localconf.php";s:4:"50ae";s:14:"ext_tables.php";s:4:"aa3a";s:14:"ext_tables.sql";s:4:"8641";s:27:"icon_tx_timtab_blogroll.gif";s:4:"7d43";s:23:"icon_tx_timtab_post.gif";s:4:"afd3";s:26:"icon_tx_timtab_post__h.gif";s:4:"3e59";s:13:"locallang.xml";s:4:"4ce2";s:16:"locallang_db.xml";s:4:"a6b3";s:7:"tca.php";s:4:"bdf2";s:8:"test.php";s:4:"7be7";s:9:"test2.php";s:4:"c928";s:25:"3rdparty/Snoopy.class.php";s:4:"7c1e";s:20:"3rdparty/lib.ixr.php";s:4:"8ff9";s:14:"doc/manual.sxw";s:4:"f413";s:31:"doc/examples/timtab-kubrick.t3d";s:4:"ae5c";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:27:"pi1/class.tx_timtab_pi1.php";s:4:"bada";s:35:"pi1/class.tx_timtab_pi1_wizicon.php";s:4:"fc65";s:17:"pi1/locallang.php";s:4:"3a3b";s:27:"pi2/class.tx_timtab_pi2.php";s:4:"9282";s:38:"pi2/class.tx_timtab_pi2_xmlrpcauth.php";s:4:"ecba";s:40:"pi2/class.tx_timtab_pi2_xmlrpcserver.php";s:4:"e092";s:14:"pi3/ce_wiz.gif";s:4:"02b6";s:27:"pi3/class.tx_timtab_pi3.php";s:4:"357c";s:35:"pi3/class.tx_timtab_pi3_wizicon.php";s:4:"330e";s:17:"pi3/locallang.xml";s:4:"da15";s:39:"res/class.tx_timtab_realurlautoconf.php";s:4:"5a5f";s:29:"res/realurl_example_setup.php";s:4:"f7a3";s:25:"res/buttons/atom_news.gif";s:4:"5dec";s:29:"res/buttons/firefox_80x15.png";s:4:"deb3";s:26:"res/buttons/nopic_50_f.jpg";s:4:"577c";s:24:"res/buttons/rdf_news.gif";s:4:"5271";s:24:"res/buttons/rss_news.gif";s:4:"2b53";s:29:"res/buttons/timtab_button.gif";s:4:"9189";s:35:"res/buttons/typo3_button_logo_2.gif";s:4:"0fc4";s:23:"res/buttons/xfn-btn.gif";s:4:"bc51";s:33:"res/kubrick/kubrick_comments.tmpl";s:4:"023b";s:29:"res/kubrick/kubrick_main.tmpl";s:4:"718d";s:31:"res/kubrick/kubrick_single.tmpl";s:4:"a2c0";s:28:"res/kubrick/kubrick_std.tmpl";s:4:"a655";s:32:"res/kubrick/kubrick_tt_news.tmpl";s:4:"457f";s:21:"res/kubrick/style.css";s:4:"8191";s:32:"res/kubrick/images/kubrickbg.jpg";s:4:"fb89";s:37:"res/kubrick/images/kubrickbgcolor.jpg";s:4:"59ca";s:36:"res/kubrick/images/kubrickbgwide.jpg";s:4:"49ea";s:36:"res/kubrick/images/kubrickfooter.jpg";s:4:"b921";s:36:"res/kubrick/images/kubrickheader.jpg";s:4:"7627";s:28:"static/kubrick/constants.txt";s:4:"e153";s:24:"static/kubrick/setup.txt";s:4:"b727";s:27:"static/timtab/constants.txt";s:4:"fab5";s:23:"static/timtab/setup.txt";s:4:"0803";s:31:"static/webservice/constants.txt";s:4:"b4b2";s:27:"static/webservice/setup.txt";s:4:"9f22";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'sv' => '',
			'css_styled_content' => '',
			'tt_news' => '',
			'comments' => '',
			'php' => '4.1.0-6.0.0',
			'typo3' => '4.2.0-4.3.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>