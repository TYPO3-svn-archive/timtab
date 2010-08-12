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
 * realurl autoconfiguration class
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author	Lina Wolf <2010@lotypo3.de>
 * @version $Id$
 */

/**
 * realurl autoconfiguration class
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author	Lina Wolf <2010@lotypo3.de>
 * @version $Id$
 */
class tx_timtab_Realurlautoconf {

	/**
	 * Generates additional RealURL configuration and merges it with provided configuration
	 *
	 * @param array $params Default configuration
	 * @param tx_realurl_autoconfgen $pObj Parent object
	 * @return arrayUpdated configuration
	 */
	public function generateUrlWithDate($params, tx_realurl_autoconfgen $pObj) {
		$myConf = array(
			'fileName' => array (
			'defaultToHTMLsuffixOnPrev' => 1,
			'index' => array(
				'page.html' => array(
					'keyValues' => array (
						'type' => 1,
					),
				),
				'rss.xml' => array(
					'keyValues' => array (
						'type' => 100,
					),
				),
				'ping.xml' => array(
					'keyValues' => array (
						'type' => 1,
					),
				),
			),
			),
			'postVarSets'   => array(
				'_DEFAULT' => array(

					'ping' => array(
						'type' => 'single',
						'keyValues' => array(
							'type' => 200,
						),
					),
					// news archive parameters
					'archive' => array(
						array(
							'GETvar' => 'tx_ttnews[year]',
						),
						array(
							'GETvar'   => 'tx_ttnews[month]',
							'valueMap' => array(
								'january'   => '01',
								'february'  => '02',
								'march'     => '03',
								'april'     => '04',
								'may'       => '05',
								'june'      => '06',
								'july'      => '07',
								'august'    => '08',
								'september' => '09',
								'october'   => '10',
								'november'  => '11',
								'december'  => '12',
							),
						),
						array(
							'GETvar' => 'tx_ttnews[day]',
						),
					),
					'article' => array (
						array(
							'GETvar'      => 'tx_ttnews[tt_news]',
							'lookUpTable' => array(
								'table'               => 'tt_news',
								'id_field'            => 'uid',
								'alias_field'         => 'title',
								'addWhereClause'      => ' AND NOT deleted',
								'useUniqueCache'      => 1,
								'useUniqueCache_conf' => array(
									'strtolower'     => 1,
									'spaceCharacter' => '_',
								)
							),
						),
						array(
							'GETvar' => 'tx_timtab_pi2[trackback]',
						),
					),
					// news categories
					'category' => array(
						array(
							'GETvar'      => 'tx_ttnews[cat]',
							'lookUpTable' => array(
								'table'               => 'tt_news_cat',
								'id_field'            => 'uid',
								'alias_field'         => 'title',
								'addWhereClause'      => ' AND NOT deleted',
								'useUniqueCache'      => 1,
								'useUniqueCache_conf' => array(
									'strtolower'     => 1,
									'spaceCharacter' => '_',
								),
							),
						),
					),
					// news pagebrowser
					'browse' => array(
						array(
							'GETvar' => 'tx_ttnews[pointer]',
						),
					),
			),
			),
		);
		return array_merge_recursive($params['config'], $myConf);
	}
}
?>