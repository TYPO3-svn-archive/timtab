<?php

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

//get EXT path
$PATH_timtab = t3lib_extMgm::extPath('timtab');

if (TYPO3_MODE == 'FE')	{
	require_once($PATH_timtab.'lib/class.tx_timtab_hook_ttnews.php');
	require_once($PATH_timtab.'lib/class.tx_timtab_hook_comments.php');
} else {
	require_once($PATH_timtab.'lib/class.tx_timtab_be.php');
}

//presetting userTS
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_timtab_blogroll = 1');

//Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','tt_content.CSS_editor.ch.tx_timtab_pi1 = < plugin.tx_timtab_pi1.CSS_editor',43);

//listing Blogroll Links in Web->Page view
$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_timtab_blogroll'][0] = array(
	'fList' => 'name,url',
	'icon' => true
);

//adding plugins
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_timtab_pi1.php','_pi1','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_timtab_pi2.php','_pi2','list_type',0);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_timtab_pi3.php','_pi3','list_type',1);

//registering for several hooks
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][]        = 'tx_timtab_hook_ttnews';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_timtab_be'; 

# RealURL Autokonfiguration
$TYPO3_CONF_VARS['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['timtab'] 
	= 'EXT:timtab/res/class.tx_timtab_realurlautoconf.php:tx_timtab_realurlautoconf->generateUrlWithDate';
	
#Hook for closing comments
$TYPO3_CONF_VARS['EXTCONF']['comments']['closeCommentsAfter'][] = 'EXT:timtab/lib/class.tx_timtab_hook_comments.php:tx_timtab_hook_comments->closeComments';
# Hook for addittional markers
$TYPO3_CONF_VARS['EXTCONF']['comments']['comments_getComments']['timtab'] = 'EXT:timtab/lib/class.tx_timtab_hook_comments.php:&tx_timtab_hook_comments->comments_getComments';

?>