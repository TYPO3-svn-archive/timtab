<?php

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

//get EXT path
$PATH_timtab = t3lib_extMgm::extPath('timtab');

if (TYPO3_MODE == 'FE')	{
	require_once($PATH_timtab.'lib/class.tx_timtab_hook_ttnews.php');
	require_once($PATH_timtab.'lib/class.tx_timtab_hook_comments.php');
	require_once($PATH_timtab.'widgets/blogroll/class.tx_timtab_blogroll.php');
	require_once($PATH_timtab.'widgets/latestcomments/class.tx_timtab_latestcomments.php');
	require_once($PATH_timtab.'widgets/calendar/class.tx_timtab_calendar.php');
} else {
	require_once($PATH_timtab.'lib/class.tx_timtab_be.php');
}

//
$TYPO3_CONF_VARS['FE']['eID_include']['timtab_calendar'] = 'EXT:timtab/widgets/calendar/class.tx_timtab_calendar_eID.php';

//presetting userTS
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_timtab_blogroll = 1
');
t3lib_extMgm::addPageTSConfig('
	mod.wizards.newContentElement.wizardItems {
		special {
			elements {
				timtab_pi3 {
					icon = ../typo3conf/ext/timtab/res/gfx/ce_wiz_calendar.gif
					title = LLL:EXT:timtab/locallang.xml:calendar_title
					description = LLL:EXT:timtab/locallang.xml:calendar_description
					tt_content_defValues {
						CType = list
						list_type = timtab_pi3
					}
				}
			}
      show := addToList(timtab_pi3)
		}
		timtab {
			header = LLL:EXT:timtab/locallang.xml:pi1_title
			elements {
				catmenu {
					icon = ../typo3conf/ext/timtab/res/gfx/ce_wiz_catmenu.gif
					title = LLL:EXT:timtab/locallang.xml:catmenu_title
					description = LLL:EXT:timtab/locallang.xml:catmenu_description
					tt_content_defValues {
						CType = timtab_pi1
						tx_timtab_widget_type = calendar
					}
				}
				latestcomments {
					icon = ../typo3conf/ext/timtab/res/gfx/ce_wiz_latestcomments.gif
					title = LLL:EXT:timtab/locallang.xml:latestcomments_title
					description = LLL:EXT:timtab/locallang.xml:latestcomments_description
					tt_content_defValues {
						CType = timtab_pi1
						tx_timtab_widget_type = calendar
					}
				}
				blogroll {
					icon = ../typo3conf/ext/timtab/res/gfx/ce_wiz_blogroll.gif
					title = LLL:EXT:timtab/locallang.xml:blogroll_title
					description = LLL:EXT:timtab/locallang.xml:blogroll_description
					tt_content_defValues {
						CType = timtab_pi1
						tx_timtab_widget_type = blogroll
					}
				}
				calendar {
					icon = ../typo3conf/ext/timtab/res/gfx/ce_wiz_calendar.gif
					title = LLL:EXT:timtab/locallang.xml:calendar_title
					description = LLL:EXT:timtab/locallang.xml:calendar_description
					tt_content_defValues {
						CType = timtab_pi1
						tx_timtab_widget_type = calendar
					}
				}
			}
			show = catmenu,latestcomments,blogroll,calendar
		}
	}
');

//Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','tt_content.CSS_editor.ch.tx_timtab_pi1 = < plugin.tx_timtab_pi1.CSS_editor',43);

//listing Blogroll Links in Web->Page view
$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_timtab_blogroll'][0] = array(
	'fList' => 'name,url',
	'icon' => true
);

//adding plugins

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_timtab_pi1.php','_pi1','CType',0);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_timtab_pi2.php','_pi2','list_type',0);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_timtab_pi3.php','_pi3','list_type',1);

# RealURL Autokonfiguration
$TYPO3_CONF_VARS['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['timtab'] 
	= 'EXT:timtab/res/class.tx_timtab_realurlautoconf.php:tx_timtab_realurlautoconf->generateUrlWithDate';

# Hook for creating additional tt_news markers
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][]  = 'tx_timtab_hook_ttnews';

# Hook for postprocessing record after saving in be
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_timtab_be'; 
	
#Hook for closing comments
$TYPO3_CONF_VARS['EXTCONF']['comments']['closeCommentsAfter'][] = 'EXT:timtab/lib/class.tx_timtab_hook_comments.php:tx_timtab_hook_comments->closeComments';
# Hook for addittional markers
$TYPO3_CONF_VARS['EXTCONF']['comments']['comments_getComments']['timtab'] = 'EXT:timtab/lib/class.tx_timtab_hook_comments.php:&tx_timtab_hook_comments->comments_getComments';

# Registering build-in widgets
$TYPO3_CONF_VARS['EXTCONF']['timtab']['renderWidgets'][] = 'EXT:timtab/widgets/blogroll/class.tx_timtab_blogroll.php:&tx_timtab_blogroll->render';
$TYPO3_CONF_VARS['EXTCONF']['timtab']['renderWidgets'][] = 'EXT:timtab/widgets/latestcomments/class.tx_timtab_latestcomments.php:&tx_timtab_latestcomments->render';
$TYPO3_CONF_VARS['EXTCONF']['timtab']['renderWidgets'][] = 'EXT:timtab/widgets/calendar/class.tx_timtab_calendar.php:&tx_timtab_calendar->render';

?>