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
 * Widget 'blogroll' for the 'TIMTAB' extension.
 * based on code from Ingo Renner
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author	Timo Webler <timo.webler@dkd.de>
 * @version $Id:$
 */

require_once(t3lib_extMgm::extPath('timtab', 'widgets/interface.tx_timtab_widget_interface.php'));

/**
 * Widget 'blogroll' for the 'TIMTAB' extension.
 * based on code from Ingo Renner
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author	Timo Webler <timo.webler@dkd.de>
 */
class tx_timtab_widgets_Catmenu implements tx_timtab_widget_Interface {

	/**
	 * render the widget
	 *
	 * @param array $configuration plugin configuration
	 * @param string $pidList pid list
	 * @param tx_timtab_pi1 $referenz plugin object
	 * @return string
	 */
	public function render(array $configuration, $pidList, tx_timtab_pi1 $referenz) {
		$content = $referenz->cObj->cObjGetSingle(
			$configuration['widgets.']['catMenu.']['renderCObject'],
			$configuration['widgets.']['catMenu.']['renderCObject.']
		);
		return $content;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/catmenu/class.tx_timtab_catmenu.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/catmenu/class.tx_timtab_catmenu.php']);
}
?>