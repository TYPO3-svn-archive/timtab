<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Lina Wolf <2010@lotypo3.de>
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
 * flexform helper class
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author	Timo Webler <timo.webler@dkd.de>
 * @version $Id$
 */

/**
 * flexform helper class
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author	Timo Webler <timo.webler@dkd.de>
 */
class tx_timtab_Flexform {


	/**
	 * get the available widgets for timtab
	 *
	 * @param array $config Konfiguration
	 * @param t3lib_TCEforms $tca t3lib_TCEforms 	instance
	 * @return array
	 */
	public function getWidgets($config, t3lib_TCEforms $tca) {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['timtab']['renderWidgets'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['timtab']['renderWidgets'] as $type => $configuration) {
				$config['items'][] = array(
					$tca->sL($configuration['label']),
					$type
				);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/class.tx_timtab_flexform.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/class.tx_timtab_flexform.php']);
}
?>