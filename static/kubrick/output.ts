
# posts list
lib.list < plugin.tt_news 
lib.list {
  code >
  code = LIST
}

#post single view
lib.single = COA
lib.single {
	10 < plugin.tt_news 
	10 {
	  code >
	  code = SINGLE
	}
	20 < plugin.tx_comments_pi1
	20 {
		templateFile = {$page.file.template_comments}
	}
}

# archives
lib.archives < plugin.tt_news 
lib.archives {
  code >
  code = AMENU
  # Fix for Bug #14715, Bugtracker
  categoryMode = 0
}

lib.blogRoll = COA
lib.blogRoll {
	pidList = {$plugin.tx_timtab.pidStoreBlogroll}
	10 < plugin.tx_timtab_pi1
	10 {
			widgetType = blogroll
	}
	stdWrap.wrap = <li><h2>Blogroll</h2>|</li>
	stdWrap.required = 1
}

lib.categories = COA
lib.categories {
	pidList =  {$plugin.tx_timtab.pidStorePosts}
	10 < plugin.tx_timtab_pi1
	10.widgetType = catMenu 
	stdWrap.wrap = <li><h2>Categories</h2>|</li>
	stdWrap.required = 1
}

/*
* Copy all Marks such that changes are considered
*/

lib.mainContent = COA
lib.mainContent {
 10 < styles.content.get
}

lib.rightContent = COA
lib.rightContent {
 10 < styles.content.getRight
}