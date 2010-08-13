<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Timo Webler <timo.webler@dkd.de>
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
 * class.tx_timtab_hooks_befunc.php
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Timo Webler <timo.webler@dkd.de>
 * @version $Id$
 */

/**
 * Implements hooks for t3lib_befunc
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Timo Webler <timo.webler@dkd.de>
 */
class tx_timtab_hooks_BeFunc {

	/**
	 * Implementation of "getFlexFormDSClass"-Hook from t3lib_befunc::getFlexFormDS
	 *
	 * @param array $dataStructArray
	 * @param array $conf
	 * @param array $row
	 * @param string $table
	 * @param string $fieldName
	 */
	public function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, $row, $table, $fieldName) {
		if ($table != 'tt_content'|| $row['list_type'] != 'timtab_pi1' || empty($row['pi_flexform'])) {
			return;
		}

		$flexformValue = t3lib_div::xml2array($row['pi_flexform']);
		$flexformValue = $flexformValue['data']['sDEF']['lDEF']['widget']['vDEF'];

		if (!empty($flexformValue) && is_string($flexformValue)) {
			$widgetConfig = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['timtab']['renderWidgets'][$flexformValue];
			if (!empty($widgetConfig) && !empty($widgetConfig['flexform'])) {
				$file = t3lib_div::getFileAbsFileName($widgetConfig['flexform']);
				if ($file && @is_file($file)) {
					$localDataStructArray = t3lib_div::xml2array(t3lib_div::getUrl($file));
					if (is_array($localDataStructArray['sheets']) && is_array($dataStructArray['sheets'])) {
						$dataStructArray['sheets'] = t3lib_div::array_merge_recursive_overrule(
							$dataStructArray['sheets'],
							$localDataStructArray['sheets']
						);
					}
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/hooks/class.tx_timtab_hooks_befunc.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/hooks/class.tx_timtab_hooks_befunc.php']);
}
?>