/**
* Widget Calendar Konfiguration
* @author Lina Wolf
*/
lib.timtab {
	renderCalendar = TEMPLATE
	renderCalendar {
		template = FILE
		template.file = {$plugin.tx_timtab.widgets.template}
		workOnSubpart = RENDER_CALENDAR
		
		subparts {
			RENDER_DAYS_OF_WEEK = TEXT
			RENDER_DAYS_OF_WEEK.field = renderCalendarDaysOfWeek
			RENDER_MONTH = TEXT
			RENDER_MONTH.field = renderCalendarMonth
		}
		marks {
			CURRENT_MONTH = TEXT
			CURRENT_MONTH.field = currentMonth
			CURENT_YEAR = TEXT
			CURENT_YEAR.field = currentYear
			
   		 	# <a href="http://wordpress.als-webseite.de/2010/07/" title="View posts for July 2010" onclick="return calendar(2010,7,0,'')">
	   		 # &laquo; Jul   		  </a>
	   		LINK_TO_PREV_MONTH = TEXT
	   		LINK_TO_PREV_MONTH {
	   			field = unixPrevMonth
	   			date = M
	   			typolink{
	   				parameter = {$plugin.tx_timtab.homePid}
					additionalParams.dataWrap = &tx_ttnews[pS]={field:unixPrevMonth}&tx_ttnews[pL]={field:unixPrevMonthEnd}&tx_ttnews[arc]=1
					useCacheHash = 1
					title.noTrimWrap = |View posts for ||
					title.field = unixPrevMonth
					title.date = F Y
					ATagParams.dataWrap = onclick="return timtab_calendar_ajax({field:unixPrevMonth},'')"
				}
				fieldRequired = unixPrevMonth
				innerWrap = &laquo;&#32;|
	   		}		
	   		LINK_TO_NEXT_MONTH = TEXT
	   		LINK_TO_NEXT_MONTH {
	   			field = unixNextMonth
	   			date = M
	   			typolink {
	   				parameter = {$plugin.tx_timtab.homePid}
					additionalParams.dataWrap = &tx_ttnews[pS]={field:unixNextMonth}&tx_ttnews[pL]={field:unixNextMonthEnd}&tx_ttnews[arc]=1
					useCacheHash = 1
					title.noTrimWrap = |View posts for ||
					title.field = unixNextMonth
					title.date = F Y
					ATagParams.dataWrap = onclick="return timtab_calendar_ajax({field:unixNextMonth},'')"
				}
				fieldRequired = unixNextMonth
				innerWrap = |&#32;&raquo;
	   		}
			LINK_TO_CURRENT_MONTH = TEXT
	   		LINK_TO_CURRENT_MONTH {
				#field = unixCurrentMonth
				#date = M
				value = &laquo;-&raquo;
	   			typolink {
	   				parameter = {$plugin.tx_timtab.homePid}
					additionalParams.dataWrap = &tx_ttnews[pS]={field:unixCurrentMonth}&tx_ttnews[pL]={field:cunixCurrentMonthEnd}&tx_ttnews[arc]=1
					useCacheHash = 1
					title.noTrimWrap = |View posts for ||
					title.field = unixCurrentMonth
					title.date = F Y
					ATagParams.dataWrap = onclick="return timtab_calendar_ajax({field:unixNextMonth},'')"
				}
				fieldRequired = unixCurrentMonth
				innerWrap = |&#32;&raquo;
	   		}
		}
	}
	renderCalendarDaysOfWeek = TEMPLATE
	renderCalendarDaysOfWeek {
		template = FILE
		template.file = {$plugin.tx_timtab.widgets.template}
		workOnSubpart = RENDER_DAYS_OF_WEEK
		marks {
			DAY_OF_WEEK_LANG = TEXT
			DAY_OF_WEEK_LANG {
				field = dayOfWeek
			}
			DAY_OF_WEEK_SHORT < DAY_OF_WEEK_LANG
			DAY_OF_WEEK_SHORT.crop = 1
		}
		template = FILE
		template.file = {$plugin.tx_timtab.widgets.template}
		workOnSubpart = RENDER_DAYS_OF_WEEK
		
		marks {
			DAY_OF_WEEK_LANG = TEXT
			DAY_OF_WEEK_LANG.field = dayOfWeek
			DAY_OF_WEEK_SHORT = TEXT
			DAY_OF_WEEK_SHORT{
				field = dayOfWeek
				crop = 1
				case = upper
			}
		}
	}
	renderCalendarMonth = TEMPLATE
	renderCalendarMonth {
		template = FILE
		template.file = {$plugin.tx_timtab.widgets.template}
		workOnSubpart = RENDER_MONTH
		
		subparts {
			RENDER_WEEK = TEXT
			RENDER_WEEK.field = renderCalendarWeek
		}
	}
	renderCalendarWeek = TEMPLATE
	renderCalendarWeek {
		template = FILE
		template.file = {$plugin.tx_timtab.widgets.template}
		workOnSubpart = RENDER_WEEK
		
		subparts {
			RENDER_SPACE_BEFORE_DAYS = COA
			RENDER_SPACE_BEFORE_DAYS {
				if.isTrue.field = spaceBeforeDays
				10 = TEMPLATE
				10 {
					template = FILE
					template.file = {$plugin.tx_timtab.widgets.template}
					workOnSubpart = RENDER_SPACE_BEFORE_DAYS
					marks {
						SIZE = TEXT
						SIZE.field = spaceBeforeDays
					}
				}
			}
			RENDER_CALENDAR_DAY = TEXT
			RENDER_CALENDAR_DAY.field = renderCalendarDay
			RENDER_SPACE_AFTER_DAYS = COA
			RENDER_SPACE_AFTER_DAYS {
				if.isTrue.field = spaceAfterDays
				10 = TEMPLATE
				10 {
					template = FILE
					template.file = {$plugin.tx_timtab.widgets.template}
					workOnSubpart = RENDER_SPACE_AFTER_DAYS
					marks {
						SIZE = TEXT
						SIZE.field = spaceAfterDays
					}
				}
			}
		}
	}
	renderCalendarDay = COA
	renderCalendarDay {
		#if day had no posts
		10 = COA
		10 {
			if.isFalse.field = hasDayPosts
			10 = TEMPLATE
			10 {
				template = FILE
				template.file = {$plugin.tx_timtab.widgets.template}
				workOnSubpart = RENDER_CALENDAR_DAY
				
				marks {
					DAY = TEXT
					DAY.field = day
				}
			}
		}
		#day with posts
		20 = COA
		20 {
			if.isTrue.field = hasDayPosts
			10 = TEMPLATE
			10 {
				template = FILE
				template.file = {$plugin.tx_timtab.widgets.template}
				workOnSubpart = RENDER_CALENDAR_DAY
				
				marks {
					DAY = TEXT
					DAY {
						field = day
						typolink.parameter = {$plugin.tx_timtab.homePid}
						typolink.additionalParams.dataWrap = &tx_ttnews[pS]={field:startUnixTime}&tx_ttnews[pL]={field:endUnixTime}&tx_ttnews[arc]=1
						typolink.useCacheHash = 1
						typolink.title.dataWrap = Posts:{field:renderCalenderPosts}
					}
				}
			}
		}
	}
	renderCalenderPosts = TEXT
	renderCalenderPosts{
		field = title
		noTrimWrap = ||, |
	}	
	renderCalenderHeader = COA
	renderCalenderHeader {
		wrap = <!-- timtab calendar --> |
		10 = TEXT
		10 {
			data = path:EXT:timtab/widgets/calendar/calendar.css
			wrap = <link rel="stylesheet" href="|" type="text/css" media="screen" title="no title" charset="utf-8"/>
		}
		20 = TEXT
		20 {
			data = path:EXT:timtab/widgets/calendar/microajax.js
			wrap = <script type="text/javascript" src="|"></script>
		}
		30 = TEXT
		30 {
			value (
				<script type="text/javascript" charset="utf-8">
				/*<![CDATA[ */
					function timtab_calendar_ajax (unixdate)
					{
						microAjax ('index.php?eID=timtab_calendar&startdate=' + unixdate, function(response) { document.getElementById ('timtab-calendar-ajax').innerHTML = response});
						return false;
					}
				/*]]>*/
				</script>
			)
		}
	}
}

plugin.tx_timtab_pi1 {
	widgets {
		calendar {
			conf {
				# Week should start with monday
				weekBegins = 1
			}
			renderCalendar =< lib.timtab.renderCalendar
			renderCalendarDaysOfWeek =< lib.timtab.renderCalendarDaysOfWeek
			renderCalendarMonth =< lib.timtab.renderCalendarMonth
			renderCalendarWeek =< lib.timtab.renderCalendarWeek
			renderCalendarDay =< lib.timtab.renderCalendarDay
			renderCalenderPosts =< lib.timtab.renderCalenderPosts
			renderCalenderHeader =< lib.timtab.renderCalenderHeader
		}
	}
}