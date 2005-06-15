<?php
//
//	$Id$
//

if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE!='BE')	{
require_once(t3lib_extMgm::extPath('timtab').'class.tx_timtab.php');
}

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_timtab_blogroll=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_timtab_pi1 = < plugin.tx_timtab_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_timtab_pi1.php','_pi1','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_timtab_pi2.php','_pi2','list_type',0);

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][] = 'tx_timtab';
$TYPO3_CONF_VARS['EXTCONF']['ve_guestbook']['extraItemMarkerHook'][] = 'tx_timtab';
$TYPO3_CONF_VARS['EXTCONF']['ve_guestbook']['postEntryInsertedHook'][] = 'tx_timtab'; 
?>