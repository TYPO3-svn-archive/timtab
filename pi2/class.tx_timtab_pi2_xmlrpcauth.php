<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Ingo Renner (typo3@ingo-renner.com)
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
 * Authentification class for the XML-RPC Server 
 *
 * @author    Ingo Renner <typo3@ingo-renner.com>
 */

require_once(PATH_t3lib.'class.t3lib_userauth.php');
require_once(PATH_t3lib.'class.t3lib_userauthgroup.php');
require_once(PATH_t3lib.'class.t3lib_beuserauth.php');

class tx_timtab_pi2_xmlrpcAuth extends t3lib_beuserauth {
	var $loginType = 'BE';
	var $security_level = 'normal';
	var $writeAttemptLog = true;
	var $writeDevLog = true;
	
	var $xmlrpcLoginData;
	var $xmlrpcAuthInfo;
	var $xmlrpcUser;
	

	/**
	 * initialize login data
	 * 
	 * @param string username the username
	 * @param string password clear text password
	 * @return void
	 */
	function initAuth($username, $password) {
		$this->xmlrpcLoginData = array(
			'uname'  => $username,
			'uident' => md5($password),
			'status' => 'login',
		);
		
		$this->xmlrpcAuthInfo = $this->getAuthInfoArray();
	}

	/**
	 * get a BE user, will return false on failure
	 * 
	 * @return user object on success, false otherwise
	 */
	function getUser() {
		
		if(is_object($serviceObj = t3lib_div::makeInstanceService('auth', 'getUserBE'))) {
		
			$serviceObj->initAuth('getUserBE', $this->xmlrpcLoginData, $this->xmlrpcAuthInfo, $this);
			
			//get a login user
			if($this->xmlrpcUser = $serviceObj->getUser()) {
				return $this->xmlrpcUser;
			} 		
		} 
		
		return false;
	}

	/**
	 * authentify user with username, password
	 * 
	 * @return boolean 
	 */
	function authUser() {
		$OK = false;
		
		if(is_object($serviceObj = t3lib_div::makeInstanceService('auth', 'authUserBE'))) {
		
			$serviceObj->initAuth('authUserBE', $this->xmlrpcLoginData, $this->xmlrpcAuthInfo, $this);
			
			//auth user
			$OK = $serviceObj->authUser($this->xmlrpcUser);								
		}
		
		return $OK;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2_xmlrpcauth.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi2/class.tx_timtab_pi2_xmlrpcauth.php']);
}
?>
