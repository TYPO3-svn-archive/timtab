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
 * Plugin 'calendar' for the 'timtab' extension.
 * Code shamlesly taken from wordpress
 *
 * $Id$
 *
 * @author	Ingo Renner <typo3@ingo-renner.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   56: class tx_timtab_pi3 extends tslib_pibase
 *   69:     function main($content, $conf)
 *   87:     function init($conf)
 *  118:     function getCalendar()
 *  280:     function getCurrentTime($gmt = false)
 *  296:     function getDaysWithPosts($monthBeginn)
 *  335:     function getMonthLink($timestamp, $now)
 *  369:     function getDayLink($timestamp, $day, $title)
 *  398:     function getWeekdays()
 *  418:     function calendarWeekMod($num)
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_timtab_pi3 extends tslib_pibase {
	var $prefixId = 'tx_timtab_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_timtab_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey = 'timtab';	// The extension key.
	var $enableFields;

	/**
	 * main funtction for blogroll
	 *
	 * @param	string		plugin output is added to this
	 * @param	array		configuration array
	 * @return	string		complete content generated by the blogroll plugin
	 */
	function main($content, $conf)	{
		$this->init($conf);

		$calendar = $this->getCalendar();

		$content  = $this->cObj->stdWrap($calendar, $this->conf['header_stdWrap.']);

		if($this->conf['dontWrapInDiv'] == 1) {
			return $content;
		} else {
			return $this->pi_wrapInBaseClass($content);
		}
	}

	/**
	 * initializes the configuration for this plugin
	 *
	 * @param	array		configuration array
	 * @return	void
	 */
	function init($conf) {
		$this->conf = array_merge($this->conf, $conf);
		$this->conf['allowCaching'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tt_news.']['allowCaching'];
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// pidList is the pid/list of pids from where to fetch the faq items.
		$cePidList = $this->cObj->data['pages']; //ce = Content Element
		$pidList   = $cePidList ?
			$cePidList :
			trim($this->cObj->stdWrap(
				$this->conf['pid_list'], $this->conf['pid_list.']
			));

		$this->conf['pidList'] = $pidList ?
			implode(t3lib_div::intExplode(',', $pidList), ',') :
			$GLOBALS['TSFE']->id;

		$this->enableFields = $this->cObj->enableFields('tt_news')
							.' AND tt_news.type = 3'
							.' AND tt_news.pid IN('.$this->conf['pidList'].')';


		unset($this->conf['pid_list']);
	}

	/**
	 * renders calendar which shows days with posts, addopted from wordpress
	 *
	 * @return	string		the html for the calendar
	 */
	function getCalendar() {
		// Quick check. If we have no posts at all, abort!
		$check = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid',
			'tt_news',
			'type = 3'.$this->cObj->enableFields('tt_news'),
			'',
			'datetime DESC',
			1
		);

		if(empty($check)) {
			return '';
		}

		// week_begins = 0 stands for sunday
		$weekBegins = $this->conf['week_begins'];
		$addHours   = $this->conf['gmt_offset'];
		$addMinutes = intval(60 * ($this->conf['gmt_offset'] - $addHours));

		// Let's figure out when we are
		$newsGET = t3lib_div::_GET('tx_ttnews');
		if (!empty($newsGET)) {
			$thisYear  = $newsGET['year'];
			$thisMonth = intval($newsGET['month']);
		} else {
			$thisYear  = gmdate('Y', $this->getCurrentTime() + $this->conf['gmt_offset'] * 3600);
			$thisMonth = gmdate('n', $this->getCurrentTime() + $this->conf['gmt_offset'] * 3600);
		}

		$unixMonth = mktime(0, 0 , 0, $thisMonth, 1, $thisYear);

		// Get the next and previous month and year with at least one post
		$prevTime = $unixMonth;
		$prev = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'datetime',
			'tt_news',
			'datetime < \''.$prevTime.'\''.$this->enableFields,
			'',
			'datetime DESC',
			1
		);
		if(!empty($prev)) {
			$prev = $prev[0]['datetime'];
		}

		$nextTime = mktime(0, 0, 0, $thisMonth + 1, 1, $thisYear);
		$next = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'datetime',
			'tt_news',
			'datetime > \''.$nextTime.'\''.$this->enableFields,
			'',
			'datetime ASC',
			1
		);
		if(!empty($next)) {
			$next = $next[0]['datetime'];
		}

		//beginn output
	    $content = '<table id="timtab-calendar">
	    	<caption>'.strftime('%B', $unixMonth).' '.date('Y', $unixMonth).'</caption>
	    	<thead>
	    	<tr>';

	    $week     = array();
    	$weekdays = $this->getWeekdays();

    	for($i = 0; $i <= 6; $i++) {
    		$week[] = $weekdays[($i + $weekBegins) % 7];
    	}
    	foreach ($week as $wd) {
    		$content .= "\n\t\t\t".'<th abbr="'.$wd.'" scope="col" title="'.$wd.'">'.substr($wd, 0, $this->conf['weekdayNameLength']).'</th>';
		}

		$content .= '
		</tr>
		</thead>

		<tfoot>
		<tr>';

		if ($prev) {
			$content .= "\n\t\t\t".'<td abbr="'.strftime('%b', $prev).'" colspan="3" id="prev">'
					 .$this->getMonthLink($prev, $unixMonth).'</td>';
		} else {
			$content .= "\n\t\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
		}

		$content .= "\n\t\t\t".'<td class="pad">&nbsp;</td>';

		if ($next) {
			$content .= "\n\t\t\t".'<td abbr="'.strftime('%b', $next).'" colspan="3" id="next">'
					 .$this->getMonthLink($next, $unixMonth).'</td>';
		} else {
			$content .= "\n\t\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
		}

		$content .= '
		</tr>
		</tfoot>

		<tbody>
		<tr>';

		// Get days with posts
		$daysWithPosts = $this->getDaysWithPosts($unixMonth);

		// See how much we should pad in the beginning
		$pad = $this->calendarWeekMod(date('w', $unixMonth) - $weekBegins);
		if($pad != 0) {
			$content .= "\n\t\t\t".'<td colspan="'.$pad.'" class="pad">&nbsp;</td>';
		}

		$daysInMonth = intval(date('t', $unixMonth));
		for ($day = 1; $day <= $daysInMonth; ++$day) {
			if(isset($newrow) && $newrow) {
				$content .= "\n\t\t</tr>\n\t\t<tr>\n\t\t\t";
			}
			$newrow = false;

			if($day == gmdate('j', (time() + ($addHours * 3600))) && $thisMonth == gmdate('m', time()+($addHours * 3600)) && $thisYear == gmdate('Y', time()+($addHours * 3600))) {
				$content .= '<td id="today">';
			} else {
				$content .= '<td>';
			}

			if(array_key_exists($day, $daysWithPosts)) {
				// any posts today?
				$content .= $this->getDayLink($unixMonth, $day, $daysWithPosts[$day]);
			} else {
				$content .= $day;
			}
			$content .= '</td>';

			if (6 == $this->calendarWeekMod(date('w', mktime(0, 0 , 0, $thisMonth, $day, $thisYear))-$weekBegins)) {
				$newrow = true;
			}
		}

		$pad = 7 - $this->calendarWeekMod(date('w', mktime(0, 0 , 0, $thisMonth, $day, $thisYear))-$weekBegins);
		if ($pad != 0 && $pad != 7) {
			$content .= "\n\t\t\t".'<td class="pad" colspan="'.$pad.'">&nbsp;</td>';
		}

		$content .= "\n\t\t</tr>\n\t\t</tbody>\n\t\t</table>";

		return $content;
	}

	/**
	 * gets the current time optionaly regarding GMT offset
	 *
	 * @param	boolean		get time without GMT offset when set to true
	 * @return	integer		the current timestamp
	 */
	function getCurrentTime($gmt = false) {
		if($gmt) {
			$time = time();
		} else {
			$time = time() + ($this->conf['gmt_offset'] * 3600);
		}

		return $time;
	}

	/**
	 * gets array with days where posts were made
	 *
	 * @param	integer		timestamp of the beginning of the month we want to get posts from
	 * @return	array		array with days where posts were made
	 */
	function getDaysWithPosts($monthBeginn) {
		$monthEnd = $monthBeginn + ((int)date('t', $monthBeginn) * 24 * 3600);

		$userAgent = t3lib_div::getIndpEnv('HTTP_USER_AGENT');
		if (strstr($userAgent, 'MSIE') || strstr(strtolower($userAgent), 'camino') || strstr(strtolower($userAgent), 'safari')) {
			//IE, Camino, Safari
			$titleSeparator = "\n";
		} else {
			//every other browser
			$titleSeparator = ', ';
		}

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'title, datetime',
			'tt_news',
			'datetime > '.$monthBeginn.' AND datetime < '.$monthEnd.$this->enableFields,
			'datetime ASC'
		);

		$daysWithPosts = array();
		foreach($result as $row) {
			$day = date('j', $row['datetime']);
			if(!empty($daysWithPosts[$day])) {
				$daysWithPosts[$day] .= $titleSeparator.$row['title'];
			} else {
				$daysWithPosts[$day] = $row['title'];
			}
		}

		return $daysWithPosts;
	}

	/**
	 * generates a typolink to the month of the given timestamp
	 *
	 * @param	integer		timestamp of the month to link to
	 * @param	integer		timestamp of the currently shown month
	 * @return	string		typolink
	 */
	function getMonthLink($timestamp, $now) {
		$urlParams = array(
			'tx_ttnews[year]'  => date('Y', $timestamp),
			'tx_ttnews[month]' => date('m', $timestamp)
		);

		$tagAttribs = ' title="'.sprintf($this->pi_getLL('view_posts'), strftime('%B %G', $timestamp)).'"';

		$conf = array(
			'useCacheHash'     => $this->conf['allowCaching'],
			'no_cache'         => !$this->conf['allowCaching'],
			'parameter'        => $GLOBALS['TSFE']->id, /*the link target*/
			'additionalParams' => $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',$urlParams,'',1).$this->pi_moreParams,
			'ATagParams'       => $tagAttribs
		);

		$link = strftime('%b', $timestamp);
		if($timestamp < $now) {
			$link = '&laquo; '.$link;
		} else {
			$link = $link.' &raquo;';
		}

		return $this->cObj->typoLink($link, $conf);
	}

	/**
	 * generates a typolink to the day of the given timestamp
	 *
	 * @param	integer		timestamp of the month to link to
	 * @param	integer		the day to link to
	 * @param	string		the title attribute for the link
	 * @return	string		typolink
	 */
	function getDayLink($timestamp, $day, $title) {
		if($day < 10) {
			$day = '0'.$day;
		}

		$urlParams = array(
			'tx_ttnews[year]'  => date('Y', $timestamp),
			'tx_ttnews[month]' => date('m', $timestamp),
			'tx_ttnews[day]'   => $day
		);

		$tagAttribs = ' title="'.$title.'"';

		$conf = array(
			'useCacheHash'     => $this->conf['allowCaching'],
			'no_cache'         => !$this->conf['allowCaching'],
			'parameter'        => $GLOBALS['TSFE']->id, /*the link target*/
			'additionalParams' => $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',$urlParams,'',1).$this->pi_moreParams,
			'ATagParams'       => $tagAttribs
		);

		return $this->cObj->typoLink($day, $conf);
	}

	/**
	 * returns an array with localized weekday names
	 *
	 * @return	array		array with localized weekday names
	 */
	function getWeekdays() {
		$week = array(
			$this->pi_getLL('sunday'),
			$this->pi_getLL('monday'),
			$this->pi_getLL('tuesday'),
			$this->pi_getLL('wednesday'),
			$this->pi_getLL('thursday'),
			$this->pi_getLL('friday'),
			$this->pi_getLL('saturday')
		);

		return $week;
	}

	/**
	 * I have no clue what this thing does (taken from wordpress)
	 *
	 * @param	integer		$num
	 * @return	integer		...
	 */
	function calendarWeekMod($num) {
		$base = 7;
		return ($num - $base * floor($num/$base));
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi3/class.tx_timtab_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/pi3/class.tx_timtab_pi3.php']);
}

?>