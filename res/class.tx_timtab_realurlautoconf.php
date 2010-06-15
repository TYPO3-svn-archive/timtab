<?php
class tx_timtab_realurlautoconf {

	/**
	 * Generates additional RealURL configuration and merges it with provided configuration
	 * @paramarray $paramsDefault configuration
	 * @param tx_realurl_autoconfgen$pObjParent object
	 * @return arrayUpdated configuration
	 * @author Lina Wolf
	 */
	function generateUrlWithDate($params, &$pObj) {
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
/*
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['linkData-PostProc'][] = 'EXT:realurl/class.tx_realurl.php:&tx_realurl->encodeSpURL';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc'][] = 'EXT:realurl/class.tx_realurl.php:&tx_realurl->decodeSpURL';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_realurl_urldecodecache'] = 'tx_realurl_urldecodecache';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_realurl_urlencodecache'] = 'tx_realurl_urlencodecache';

$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_realurl_pathsegment,alias,nav_title,title';

$TYPO3_CONF_VARS['EXTCONF']['realurl'] = array(
	'_DEFAULT' => 
);
*/
?>