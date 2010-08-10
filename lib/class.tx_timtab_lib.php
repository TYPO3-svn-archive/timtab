<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Ingo Renner (typo3@ingo-renner.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * class.tx_timtab_lib.php
 *
 * contains general purpose functions
 *
 * $Id: class.tx_timtab_lib.php 4981 2007-02-19 17:32:28Z flyguide $
 *
 * @author Ingo Renner <typo3@ingo-renner.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *
 */

require_once(PATH_t3lib.'class.t3lib_tcemain.php');

class tx_timtab_lib {

	/**
	 * explicitly clears cache for the blog page as it is not updating sometimes
	 *
	 * @param	string		comma separated list of page ids
	 * @return	void
	 */
	function clearPageCache($pageIDs) {
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->admin = 1;

		$clearCachePages = t3lib_div::intExplode(',', $pageIDs);
		foreach($clearCachePages as $page) {
			$tce->clear_cacheCmd((int) $page);
		}
		$tce->admin = 0;
	}
	
		
	/**
	* borrowed from tt_news
	*/
	function getSingleViewLink($cObj, $uid, $section='', $urlOnly = false) {
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tt_news.'];
		$where = 'uid="'.intval($uid).'" '.$this->cObj->enableFields('tt_news').' ';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid,datetime',
			'tt_news',
			$where
		);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$token = '|';
			$singlePid = $conf['singlePid'];
			$allowCaching = 1;
			$piVars = array();
			$piVars['year'] = date('Y', $row['datetime']);
			$piVars['month'] = date('m', $row['datetime']);
			
			
			$piVarsArray = array('backPid' => ($conf['dontUseBackPid'] ? null : $config['backPid']),
					'year' => ($conf['dontUseBackPid'] ? null : ($piVars['year'] ? $piVars['year'] : null)),
					'month' => ($conf['dontUseBackPid'] ? null : ($piVars['month'] ? $piVars['month'] : null)));
			
			if (! $conf['useHRDatesSingleWithoutDay']) {
				$piVars['day'] = date('d', $row['datetime']);
			}
			
			if ($conf['useHRDates']) {
				$piVarsArray['pS'] = null;
				$piVarsArray['pL'] = null;
				$piVarsArray['arc'] = null;
				if ($conf['useHRDatesSingle']) {
					$tmpY = $piVars['year'];
					$tmpM = $piVars['month'];
					$tmpD = $piVars['day'];
	
					$piVarsArray['year'] = $piVars['year'];
					$piVarsArray['month'] = $piVars['month'];
					$piVarsArray['day'] = ($piVars['day'] ? $piVars['day'] : null);
				}
			} else {
				$piVarsArray['year'] = null;
				$piVarsArray['month'] = null;
			}
	
			$piVarsArray['tt_news'] = $row['uid'];

			$additionalParams = '';
			foreach($piVarsArray AS $key => $value) {
				if (!is_null($value)){
					$additionalParams .= '&tx_ttnews['.$key.']='.$value;
				}
			}
			$params = array(
				'additionalParams' => $additionalParams,
				'no_cache' => $GLOBALS['TSFE']->no_cache,
				'parameter' => $singlePid,
				'useCacheHash' => !$GLOBALS['TSFE']->no_cache,
				'section' => $section
			);
			
			$linkWrap = $cObj->typolink($token, $params);
			$url = $cObj->lastTypoLinkUrl;
	
			// hook for processing of links
			/*
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['getSingleViewLinkHook'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['getSingleViewLinkHook'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$params = array('singlePid' => &$singlePid, 'row' => &$row, 'piVarsArray' => $piVarsArray);
					$_procObj->processSingleViewLink($linkWrap, $url, $params, $this);
				}
			}
			*/
	
			if ($conf['useHRDates'] && $conf['useHRDatesSingle']) {
				$piVars['year'] = $tmpY;
				$piVars['month'] = $tmpM;
				$piVars['day'] = $tmpD;
			}
	
			if ($urlOnly) {
				return $url;
			} else {
				return $linkWrap;
			}
		}
		return '';
	}

	/**
	* gets the tt_news record with the given ID
	*
	* @param        integer                the tt_news uid of the record we want to get
	* @return        array                the full tt_news record
	*/
	function getPost($id) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tt_news',
			'uid = '.$id
		);

		return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_lib.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/class.tx_timtab_lib.php']);
}

?>