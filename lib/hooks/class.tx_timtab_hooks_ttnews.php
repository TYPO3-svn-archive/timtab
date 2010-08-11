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
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Lina Wolf <2010@lotypo3.de>
 * @author Timo Webler <timo.webler@dkd.de>
 * @version $Id:$
 */

$pathTimtab = t3lib_extMgm::extPath('timtab');
require_once($pathTimtab . 'lib/class.tx_timtab_trackback.php');

/**
 * Implements hooks for tt_news to create additional markers
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Lina Wolf <2010@lotypo3.de>
 * @author Timo Webler <timo.webler@dkd.de>
 */
class tx_timtab_hooks_Ttnews {

	/**
	 * hooks into tt_news and created additional markers
	 *
	 * @param	array		$markerArray	an array of markers coming from tt_news
	 * @param	array		$row			the current tt_news record
	 * @param	array		$lConf			the configuration coming from tt_news
	 * @param	tx_ttnews	$pObj			the parent object calling this method
	 * @return	array	processed marker array
	 */
	public function extraItemMarkerProcessor($markerArray, $row, $lConf, tx_ttnews $pObj) {
		$conf         = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.'];
		$conf['data'] = $row;

		//trackback Link Generation
		$tb = t3lib_div::makeInstance('tx_timtab_trackback');
		$tb->initFe($conf, $conf['data']);

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