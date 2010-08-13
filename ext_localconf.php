<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Lina Wolf (2010@lotypo3.de)
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
 * ext_localconf file for timtab
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Ingo Renner <typo3@ingo-renner.com>
 * @author	Lina Wolf <2010@lotypo3.de>
 * @author	Timo Webler <timo.webler@dkd.de>
 * @version $Id$
 */

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

//presetting userTS
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_timtab_blogroll = 1
');

//Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', 'tt_content.CSS_editor.ch.tx_timtab_pi1 = < plugin.tx_timtab_pi1.CSS_editor', 43);

//listing Blogroll Links in Web->Page view
$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_timtab_blogroll'][0] = array(
	'fList' => 'name,url',
	'icon' => TRUE
);

$timtabRelPath = t3lib_extMgm::extRelPath('timtab');

t3lib_extMgm::addPageTSConfig('
	mod.wizards.newContentElement.wizardItems {
		timtab {
			header = LLL:EXT:timtab/locallang.xml:pi1_title
			elements {
				widget {
					icon = ' . $timtabRelPath . 'res/gfx/ce_wiz.gif
					title = LLL:EXT:timtab/locallang.xml:pi1_title
					description = LLL:EXT:timtab/locallang.xml:pi1_plus_wiz_description
					tt_content_defValues {
						CType = list
						list_type = timtab_pi1
					}
				}
				catmenu {
					icon = ' . $timtabRelPath . 'res/gfx/ce_wiz_catmenu.gif
					title = LLL:EXT:timtab/locallang.xml:catmenu_title
					description = LLL:EXT:timtab/locallang.xml:catmenu_description
					tt_content_defValues {
						CType = list
						list_type = timtab_pi1
						pi_flexform (
							<T3FlexForms>
								<data>
									<sheet index="sDEF">
										<language index="lDEF">
											<field index="widget">
												<value index="vDEF">catmenu</value>
											</field>
										</language>
									</sheet>
								</data>
							</T3FlexForms>
						)
					}
				}
				latestcomments {
					icon = ' . $timtabRelPath . 'res/gfx/ce_wiz_latestcomments.gif
					title = LLL:EXT:timtab/locallang.xml:latestcomments_title
					description = LLL:EXT:timtab/locallang.xml:latestcomments_description
					tt_content_defValues {
						CType = list
						list_type = timtab_pi1
						pi_flexform (
							<T3FlexForms>
								<data>
									<sheet index="sDEF">
										<language index="lDEF">
											<field index="widget">
												<value index="vDEF">latestcomments</value>
											</field>
										</language>
									</sheet>
								</data>
							</T3FlexForms>
						)
					}
				}
				blogroll {
					icon = ' . $timtabRelPath . 'res/gfx/ce_wiz_blogroll.gif
					title = LLL:EXT:timtab/locallang.xml:blogroll_title
					description = LLL:EXT:timtab/locallang.xml:blogroll_description
					tt_content_defValues {
						CType = list
						list_type = timtab_pi1
						pi_flexform (
							<T3FlexForms>
								<data>
									<sheet index="sDEF">
										<language index="lDEF">
											<field index="widget">
												<value index="vDEF">blogroll</value>
											</field>
										</language>
									</sheet>
								</data>
							</T3FlexForms>
						)
					}
				}
				calendar {
					icon = ' . $timtabRelPath . 'res/gfx/ce_wiz_calendar.gif
					title = LLL:EXT:timtab/locallang.xml:calendar_title
					description = LLL:EXT:timtab/locallang.xml:calendar_description
					tt_content_defValues {
						CType = list
						list_type = timtab_pi1
						pi_flexform (
							<T3FlexForms>
								<data>
									<sheet index="sDEF">
										<language index="lDEF">
											<field index="widget">
												<value index="vDEF">calendar</value>
											</field>
										</language>
									</sheet>
								</data>
							</T3FlexForms>
						)
					}
				}
			}
			show = widget,catmenu,latestcomments,blogroll,calendar
		}
	}
');

// adding plugins
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_timtab_pi1.php', '_pi1', 'list_type', 1);
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_timtab_pi2.php', '_pi2', 'list_type', 0);

// RealURL Autokonfiguration
$TYPO3_CONF_VARS['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['timtab']
	= 'EXT:timtab/res/class.tx_timtab_realurlautoconf.php:tx_timtab_Realurlautoconf->generateUrlWithDate';

// Hook for creating additional tt_news markers
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][]  = 'EXT:timtab/lib/hooks/class.tx_timtab_hooks_ttnews.php:&tx_timtab_hooks_Ttnews';

// Hook for postprocessing record after saving in be
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:timtab/lib/class.tx_timtab_be.php:&tx_timtab_Be';

//Hook for closing comments
$TYPO3_CONF_VARS['EXTCONF']['comments']['closeCommentsAfter']['timtab'] =
	'EXT:timtab/lib/hooks/class.tx_timtab_hooks_comments.php:&tx_timtab_hooks_Comments->closeComments';
// Hook for addittional markers
$TYPO3_CONF_VARS['EXTCONF']['comments']['comments_getComments']['timtab'] =
	'EXT:timtab/lib/hooks/class.tx_timtab_hooks_comments.php:&tx_timtab_hooks_Comments->getComments';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][] =
	'EXT:timtab/lib/hooks/class.tx_timtab_hooks_befunc.php:&tx_timtab_hooks_BeFunc';


// Registering build-in widgets
$TYPO3_CONF_VARS['EXTCONF']['timtab']['renderWidgets']['blogroll'] = array(
	'label' => 'LLL:EXT:timtab/locallang.xml:blogroll_title',
	'class' => 'EXT:timtab/widgets/blogroll/class.tx_timtab_widgets_blogroll.php:&tx_timtab_widgets_Blogroll'
);
$TYPO3_CONF_VARS['EXTCONF']['timtab']['renderWidgets']['latestcomments'] = array(
	'label' => 'LLL:EXT:timtab/locallang.xml:latestcomments_title',
	'class' => 'EXT:timtab/widgets/latestcomments/class.tx_timtab_widgets_latestcomments.php:&tx_timtab_widgets_Latestcomments',
	'flexform' => 'EXT:timtab/widgets/latestcomments/flexform.xml'
);
$TYPO3_CONF_VARS['EXTCONF']['timtab']['renderWidgets']['calendar'] = array(
	'label' => 'LLL:EXT:timtab/locallang.xml:calendar_title',
	'class' => 'EXT:timtab/widgets/calendar/class.tx_timtab_widgets_calendar.php:&tx_timtab_widgets_Calendar',
);
$TYPO3_CONF_VARS['EXTCONF']['timtab']['renderWidgets']['catmenu'] =  array(
	'label' => 'LLL:EXT:timtab/locallang.xml:catmenu_title',
	'class' => 'EXT:timtab/widgets/catmenu/class.tx_timtab_widgets_catmenu.php:&tx_timtab_widgets_Catmenu'
);

?>