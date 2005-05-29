<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Ingo Renner (typo3@ingo-renner.com)
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
 * Plugin 'webservices' for the 'timtab' extension.
 *
 * @author    Ingo Renner <typo3@ingo-renner.com>
 * @author    Ingo Schommer <me@chillu.com>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('timtab').'pi2/class.xmlrpcserver.php');

class tx_timtab_pi2 extends tslib_pibase {
    var $prefixId = 'tx_timtab_pi2';        // Same as class name
    var $scriptRelPath = 'pi2/class.tx_timtab_pi2.php';    // Path to this script relative to the extension dir.
    var $extKey = 'timtab';    // The extension key.
    
    /**
     * main function of pi2 creates an instance of the XML-RPC server
     */
    function main($content, $conf)    {
    	$this->conf = array_merge($conf, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.']);
        $this->pi_setPiVarDefaults();
        $this->pi_USER_INT_obj=1;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
    
    	#debug($this->conf);
    
        #$xmlrpcServer = t3lib_div::makeInstance('xmlrpcServer');
        $xmlrpcServer = new xmlrpcserver($this->conf);
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2.php']);
}

?>
