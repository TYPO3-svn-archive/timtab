<?php
/***************************************************************
*  Copyright notice
*
*  (c) 	2010 Lina Wolf (2010@lotypo3.de)
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
 * Plugin 'calendar' for the 'TIMTAB' extension.
 * Most code shamlesly taken from wordpress ;-)
 * Code based on Code of Ingo Renner from timtab v. 0.5, pi3
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Lina Wold <2010@lotypo3.de>
 * @author	Timo Webler <timo.webler@dkd.de>
 * @version $Id:$
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('timtab', 'widgets/interface.tx_timtab_widget_interface.php'));

/**
 * Plugin 'calendar' for the 'TIMTAB' extension.
 * Most code shamlesly taken from wordpress ;-)
 * Code based on Code of Ingo Renner from timtab v. 0.5, pi3
 *
 * @package TYPO3
 * @subpackage tx_timtab
 * @author Lina Wold <2010@lotypo3.de>
 * @author	Timo Webler <timo.webler@dkd.de>
 */
class tx_timtab_widgets_Calendar extends tslib_pibase implements tx_timtab_widget_Interface {

	/**
	 * Same as class name
	 *
	 * @var string
	 */
	public $prefixId = 'tx_timtab_pi1';

	/**
	 * Path to this script relative to the extension dir.
	 *
	 * @var string
	 */
	public $scriptRelPath = 'widgets/calendar/class.tx_timtab_calendar.php';

	/**
	 * The extension key.
	 *
	 * @var string
	 */
	public $extKey = 'timtab';


	/**
	 * enable fields
	 *
	 * @var string
	 */
	protected $enableFields = '';

	/**
	 * Configuring so caching is not expected. This value means that no cHash params are ever set.
	 * We do this, because it's a USER_INT object!
	 *
	 * @var integer
	 */
	public $pi_checkCHash = TRUE;


	/**
	 * widget type
	 *
	 * @var string
	 */
	protected $widgetType = 'calendar';

	/**
	 * content object
	 *
	 * @var tslib_cObj
	 */
	protected $tempCObj = NULL;


	/**
	 * timestamp
	 *
	 * @var integer
	 */
	protected $unixThisMonth = 0;

	/**
	 * timestamp
	 *
	 * @var integer
	 */
	protected $unixNextMonth = 0;

	/**
	 * timestamp
	 *
	 * @var integer
	 */
	protected $unixPrevMonth = 0;

	/**
	 * week array
	 *
	 * @var array
	 */
	protected $weekArray = array();

	/**
	 * widget configuration
	 *
	 * @var array
	 */
	protected $confWidget = array();

	/**
	 * timestamp
	 *
	 * @var integer
	 */
	protected $weekBegins = 0;

	/**
	 * render the widget
	 *
	 * @param array $configuration plugin configuration
	 * @param string $pidList pid list
	 * @param tx_timtab_pi1 $referenz plugin object
	 * @return string
	 */
	public function render(array $configuration, $pidList, tx_timtab_pi1 $referenz) {

		$this->cObj =  $referenz->cObj;
		$getParameter = t3lib_div::_GP('tx_timtab_pi1');

		$this->init($configuration, $pidList);
		$this->prepareMonth($getParameter['startdate']);

		$additionalHeader = $this->renderCalenderHeader();
		$GLOBALS['TSFE']->additionalHeaderData[] = $additionalHeader;

		$renderCalendarDaysOfWeek = $this->renderCalendarDaysOfWeek();
		$renderCalendarMonth = $this->renderCalendarMonth();
		// Since we share one cObject all recursive calls need to be made above this point
		$this->tempCObj->data = array();
		$this->tempCObj->data['pid'] = $GLOBALS['TSFE']->id;
		$this->tempCObj->data['currentYear'] = date('Y', $this->unixThisMonth);
		$this->tempCObj->data['currentMonth'] = date('F', $this->unixThisMonth);
		$this->tempCObj->data['unixCurrentMonth'] = $this->unixThisMonth;
		$this->tempCObj->data['unixCurrentMonthEnd'] = $this->addOneMonth($this->unixThisMonth);
		$this->tempCObj->data['unixPrevMonth'] = $this->unixPrevMonth;
		$this->tempCObj->data['unixPrevMonthEnd'] = $this->addOneMonth($this->unixPrevMonth);
		$this->tempCObj->data['unixNextMonth'] = $this->unixNextMonth;
		$this->tempCObj->data['unixNextMonthEnd'] = $this->addOneMonth($this->unixNextMonth);
		$this->tempCObj->data['renderCalendarDaysOfWeek'] = $renderCalendarDaysOfWeek;
		$this->tempCObj->data['renderCalendarMonth'] = $renderCalendarMonth;
		$content = $this->tempCObj->cObjGetSingle($this->confWidget['renderCalendar'], $this->confWidget['renderCalendar.']);
		return $content;
	}

	/**
	 * render calendar header
	 *
	 *@return void
	 */
	protected function renderCalenderHeader() {
		$content = $this->tempCObj->cObjGetSingle($this->confWidget['renderCalenderHeader'], $this->confWidget['renderCalenderHeader.']);
		return $content;
	}

	/**
	* Renders a month in the calendar
	*
	* @return rendered calendar month
	*/
	protected function renderCalendarMonth() {

		$renderCalendarWeek = '';
		$i = 0;
		foreach ($this->weekArray AS $weekOfYear => $week) {
			$renderCalendarWeek .= $this->renderCalendarWeek(++$i, $weekOfYear, $week);
		}
		$this->tempCObj->data = array();
		$this->tempCObj->data['renderCalendarWeek'] = $renderCalendarWeek;
		$content = $this->tempCObj->cObjGetSingle($this->confWidget['renderCalendarMonth'], $this->confWidget['renderCalendarMonth.']);
		return $content;
	}

	/**
	* Renders a week in the calendar
	*
	* @param int $weekOfMonth how maniest week of the month
	* @param int $weekOfYear how maniest week of the year (Kalenderwoche)
	* @param array $week of days in the week
	* @return rendered calendar week
	*/
	protected function renderCalendarWeek($weekOfMonth, $weekOfYear, $week) {
		$renderCalendarDay = '';
		foreach ($week AS $day => $dayData) {
			$renderCalendarDay .= $this->renderCalendarDay($day, $dayData);
		}
		$this->tempCObj->data = array();
		$this->tempCObj->data['weekOfYear'] = $weekOfYear;
		$this->tempCObj->data['weekOfMonth'] = $weekOfMonth;
		if ($weekOfMonth == 1 && count($week) < 7) {
			$this->tempCObj->data['spaceBeforeDays'] = 7 - count($week);
		} elseif (count($week) < 7) {
			$this->tempCObj->data['spaceAfterDays'] = 7 - count($week);
		}
		$this->tempCObj->data['renderCalendarDay'] = $renderCalendarDay;
		$content = $this->tempCObj->cObjGetSingle($this->confWidget['renderCalendarWeek'], $this->confWidget['renderCalendarWeek.']);
		return $content;
	}

	/**
	* Renders the one day in the calendar
	*
	* @param int $day the day
	* @param array $dayData data of the day
	* @return rendered calendar day
	*/
	protected function renderCalendarDay($day, $dayData) {
		$renderCalenderPosts = '';
		if (is_array($dayData['posts'])) {
			foreach ($dayData['posts'] AS $post) {
				$renderCalenderPosts .= $this->renderCalenderPosts($day, $dayData, $post);
			}
		}
		$this->tempCObj->data = array();
		$this->tempCObj->data['day'] = $day;
		$this->tempCObj->data['startUnixTime'] = $dayData['unixTime'];
		$this->tempCObj->data['endUnixTime'] = $dayData['unixTime'] + 24 * 3600;
		$this->tempCObj->data['dayOfWeek'] = $dayData['dayOfWeek'];
		$this->tempCObj->data['renderCalenderPosts'] = $renderCalenderPosts;
		$this->tempCObj->data['hasDayPosts'] = count($dayData['posts']) > 0;
		$content = $this->tempCObj->cObjGetSingle($this->confWidget['renderCalendarDay'], $this->confWidget['renderCalendarDay.']);
		return $content;
	}

	/**
	* Renders the posts for one day
	*
	* @param int $day the day
	* @param array $dayData data of the day
	* @param array $post selected row for the post
	* @return rendered post days
	*/
	protected function renderCalenderPosts($day, $dayData, $post) {
		$this->tempCObj->data = array();
		$this->tempCObj->data = $post;
		$content = $this->tempCObj->cObjGetSingle($this->confWidget['renderCalenderPosts'], $this->confWidget['renderCalenderPosts.']);
		return $content;
	}

	/**
	* Renders the names of the days of the week from monday to sunday (or from first to last day of the week)
	*
	* @return rendered days of the week
	*/
	protected function renderCalendarDaysOfWeek() {
		$weekDay = array(
			$this->pi_getLL('sunday'),
			$this->pi_getLL('monday'),
			$this->pi_getLL('tuesday'),
			$this->pi_getLL('wednesday'),
			$this->pi_getLL('thursday'),
			$this->pi_getLL('friday'),
			$this->pi_getLL('saturday')
		);
		$week = array();
		for ($i = 0; $i <= 6; $i++) {
			$week[] = $weekDay[($i + $this->weekBegins) % 7];
		}

		$content = '';
		foreach ($week AS $dayOfWeek) {
			$this->tempCObj->data = array();
			$this->tempCObj->data['dayOfWeek'] = $dayOfWeek;
			$content .= $this->tempCObj->cObjGetSingle($this->confWidget['renderCalendarDaysOfWeek'], $this->confWidget['renderCalendarDaysOfWeek.']);
		}
		return $content;
	}


	/**
	 * initializes the configuration for this plugin
	 *
	 * @param	array	$conf		configuration array
	 * @param	string	$pidList	pid list
	 * @return	void
	 */
	protected function init($conf, $pidList) {
		$this->conf = array_merge($this->conf, $conf);
		$this->conf['allowCaching'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tt_news.']['allowCaching'];
		$this->conf['singlePid'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.']['singlePid'];
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->conf['pidList'] = $pidList;
		$this->confWidget = $conf['widgets.']['calendar.'];

		if (!$this->cObj) {
			$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		}


		$this->enableFields = $this->cObj->enableFields('tt_news') . ' AND tt_news.type = 3';
		if ($this->conf['pidList']) {
			$this->enableFields .= ' AND tt_news.pid IN(' . $this->conf['pidList'] . ')';
		}
		$this->tempCObj = t3lib_div::makeInstance('tslib_cObj');
		$this->weekBegins = $this->confWidget['conf.']['weekBegins'];

	}

	/**
	 * prepares data of the month
	 * the first day of the month will be stored in $this->unixThisMonth as unix date
	 * the weeks of the month will be stored in $this->weekArray, an aray of weeks beeing an array of saysthe days
	 *
	 * @param int $ajaxStartDate ajax start time
	 * @return void
	 */
	protected function prepareMonth($ajaxStartDate) {
		// Let's figure out what month should be displayed
		$newsGET = t3lib_div::_GET('tx_ttnews');
		if ($ajaxStartDate) {
			$this->unixThisMonth = $ajaxStartDate;
		} elseif ($newsGET['month'] && $newsGET['year']) {
			$this->unixThisMonth = mktime(0, 0, 0, $newsGET['month'], 1, $newsGET['year']);
		} elseif ($newsGET['pS']) {
			$this->unixThisMonth = intval($newsGET['pS']);
		} else {
			$this->unixThisMonth = $this->getCurrentTime() + $this->conf['gmt_offset'] * 3600;
		}
		// unixThisMonth has to point to the first day of the month
		$this->unixThisMonth =  mktime(0, 0, 0, date('m', $this->unixThisMonth), 1, date('Y', $this->unixThisMonth));

		$this->weekArray = $this->prepareDays($this->unixThisMonth);

		$this->unixNextMonth = 0;
		$this->unixPrevMonth = 0;
		// Get the next and previous month and year with at least one post
		$prev = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'datetime',
			'tt_news',
			'datetime < \'' . $this->unixThisMonth . '\'' . $this->enableFields,
			'',
			'datetime DESC',
			1
		);
		if (!empty($prev)) {
			$this->unixPrevMonth =  mktime(0, 0, 0, date('m', $prev[0]['datetime']), 1, date('Y', $prev[0]['datetime']));
		}

		$tomorrow = $this->addOneMonth($this->unixThisMonth);
		$next = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'title,datetime',
			'tt_news',
			'datetime > \'' . $tomorrow . '\'' . $this->enableFields,
			'',
			'datetime ASC',
			1
		);
		if (!empty($next)) {
			$this->unixNextMonth = mktime(0, 0, 0, date('m', $next[0]['datetime']), 1, date('Y', $next[0]['datetime']));
		}

	}

	/**
	 * Prepares weeks and days for output in the calendar
	 *
	 * @param int $unixThisMonth month to be displayed (1..12)
	 * @return array of weeks containing array of days containing array of posts of the day if aplicable
	 */
	protected function prepareDays($unixThisMonth) {
		$daysInMonth = intval(date('t', $unixThisMonth));
		$weekArray = array();
		$week = array();
		$daysWithPosts = $this->getDaysWithPosts($unixThisMonth);
		// Create all days of the month
		for ($day = 1; $day <= $daysInMonth; ++$day) {
			$unixTime = $unixThisMonth + ($day - 1) * 24 * 3600;
			$week[$day] = array();
			$week[$day]['unixTime'] = $unixTime;
			$week[$day]['dayOfWeek'] = $this->dayOfWeek($unixTime);
			if($daysWithPosts[$day])
				$week[$day]['posts'] = $daysWithPosts[$day];
			if (6 == $week[$day]['dayOfWeek']) {
				// Start a new, empty week
				$weekArray[] = $week;
				$week = array();
			}
		}

		if($week)
			$weekArray[] = $week;
		return $weekArray;
	}

	/**
	 * find posts for the month
	 *
	 * @param int $monthBegin the first day of the month in unix time
	 * @return array of days (by number of day in month) containing an array of posts for that day only returns days for withch posts have been found
	 */
	protected function getDaysWithPosts($monthBegin) {
		//find all news of the month
		$daysInMonth = intval(date('t', $monthBegin));
		$monthEnd = $monthBegin + ($daysInMonth  * 24 * 3600);

		$where = 'datetime > ' . $monthBegin . ' AND datetime < ' . $monthEnd . $this->enableFields;
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'title, datetime',
			'tt_news',
			$where,
			'datetime ASC'
		);
		$daysWithPosts = array();
		foreach ($result as $row) {
			$day = date('j', $row['datetime']);
			if(!$daysWithPosts[$day])
				$daysWithPosts[$day] = array();
			$daysWithPosts[$day][] = $row;
		}
		return $daysWithPosts;
	}

	/**
	 * gets the current time optionaly regarding GMT offset
	 *
	 * @param	boolean	$gmt	get time without GMT offset when set to true
	 * @return	integer		the current timestamp
	 */
	protected function getCurrentTime($gmt = FALSE) {
		if ($gmt) {
			$time = time();
		} else {
			$time = time() + ($this->conf['gmt_offset'] * 3600);
		}

		return $time;
	}

	/**
	* Adds a month to the date provided
	*
	* @param integer $unixTime int the date in unix time
	* @return int $unixTime + 1 month
	*/
	protected function addOneMonth($unixTime) {
		$day = intval(date('d', $unixTime));
		$month = intval(date('n', $unixTime));
		$year = intval(date('Y', $unixTime));

		$month++;
		if ($month > 12) {
			$month = 1;
			$year++;
		}
		return mktime(0, 0, 0, $month, $day, $year);
	}


	/**
	 * Returns the number of the day of the week with weekBegin minded
	 * if week starts with sunday, sunday would be 0, monday 1
	 * if week starts with monday, sunday is 6, monday 1
	 * etc
	 * Alogorithmn:
	 * calculates dayOfweek (in numbers 0=sunday, 1=monday etc) minus the number of the day where the week begins (0 for sunday, 1 for monday, ..)
	 * adds 7 to prevent negative results and takes the modulus to bring the result between 0 and 6 (by substrakting 7 if nessesary)
	 *
	 * @param	integer	$unixTime	the day to be checked
	 * @return	integer	number of the day of the week (0..6)
	 */
	protected function dayOfWeek($unixTime) {
		return (date('w', $unixTime) - $this->weekBegins + 7) % 7;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/class.tx_timtab_calendar.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab/widgets/class.tx_timtab_calendar.php']);
}

?>