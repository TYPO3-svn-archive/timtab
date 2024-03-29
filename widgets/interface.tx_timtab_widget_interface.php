<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 timo.webler@dkd.de
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
 * Widget rendering inteface
 *
 * @package TYPO3
 * @subpackage timtab
 * @author	Timo Webler <timo.webler@dkd.de>
 * @version $Id$
 */
interface tx_timtab_widget_Interface {

	// FIXME this interface doesn't look like good OOP, by the parameters of the method it should rather be an abstract class

	/**
	 * Renders the widget
	 *
	 * @param	array	Plugin configuration
	 * @param	string	pid list
	 * @param	tx_timtab_pi1	Plugin object
	 * @return	string
	 */
	public function render(array $configuration, $pidList, tx_timtab_pi1 $reference);
}

?>