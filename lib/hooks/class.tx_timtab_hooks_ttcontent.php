<?php
/***************************************************************
*  Copyright notice
*
*  (c) 	2010 Lina Wolf (2010@lotypo3.de)
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

$pathTimtab = t3lib_extMgm::extPath('timtab');

require_once($pathTimtab . 'lib/class.tx_timtab_flexform.php');

/**
 * Implements hooks for backend view of widgets
 *
 * @package TYPO3
 * @subpackage timtab
 * @author Lina Wolf <2010@lotypo3.de>
 * @version $Id:$
 */
class tx_timtab_hooks_Ttcontent {


	/**
	 * Preprocesses the preview rendering of a content element.
	 *
	 * @param	array $parameter array of the foloowing paramters
	 *		string pObj name of the parent object, not used here
	 *		array row data of the tt_content object
	 *		array infoArr further information, not used here
	 *
	 * @return string the desired lable for the widget
	 */
	public function getPreviewInfo($parameter) {
		$parentObject = $parameter['pObj'];
		$row = $parameter['row'];
		$infoArray = $parameter['infoArr'];
		$rootlineFlexform = t3lib_div::xml2array($row['pi_flexform'], 'T3');
		$widgetType = $rootlineFlexform['data']['sDEF']['lDEF']['widget']['vDEF'];
		$widgetLable = tx_timtab_Flexform::getWidgetLabel($widgetType);
		return '<strong>' . $widgetLable . '</strong>';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/hooks/class.tx_timtab_hooks_ttcontent.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/hooks/class.tx_timtab_hooks_ttcontent.php']);
}
?> 