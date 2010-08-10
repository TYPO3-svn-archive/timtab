<?php
/***************************************************************
*  Copyright notice
*
*  (c) 	2010 Lina Wolf (2010@lotypo3.de)
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
 * class.tx_timtab_hook_ttnews.php
 *
 * Implements hooks for tt_news to create additional markers
 *
 * @author Lina Wolf <2010@lotypo3.de>
 */
 
$PATH_timtab = t3lib_extMgm::extPath('timtab');
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');
require_once($PATH_timtab.'lib/class.tx_timtab_trackback.php');

class tx_timtab_hook_ttnews extends tslib_pibase {
	
	var $cObj = null;
	
	/**
	 * hooks into tt_news and created additional markers
	 *
	 * @param	$markerArray array	an array of markers coming from tt_news
	 * @param	$row array					the current tt_news record
	 * @param	$lConf array				the configuration coming from tt_news
	 * @param	$pObj object				the parent object calling this method
	 * @return	array		processed marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$this->conf         = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.'];
		$this->conf['data'] = $row;
		$this->pObj         = &$pObj;
		$this->calledBy     = $pObj->extKey; //who is calling?

		//trackback Link Generation
		$tb = t3lib_div::makeInstance('tx_timtab_trackback');
		$tb->initFe($this->conf, $this->conf['data']);
		$plink  = $tb->getPermalink();
		$tbURL  = $tb->getTrackbackURL();
		$rdf    = $tb->getEmbeddedRdf($plink, $tbURL);
		$tbLink = $tb->getTrackbackLink();
		
		$markerArray['###BLOG_TRACKBACK_RDF###']  = $rdf;
		$markerArray['###BLOG_TRACKBACK_LINK###'] = $tbLink;
		$markerArray['###BLOG_TRACKBACK_URL###']  = $tbURL;
			
		return $markerArray;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/class.tx_timtab_hook_ttnews.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/lib/class.tx_timtab_hook_ttnews.php']);
}


?>