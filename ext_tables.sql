#
# Table structure for table 'tt_news'
#
#CREATE TABLE tt_news (
	tx_timtab_trackbacks blob NOT NULL,
	tx_timtab_tb2 tinytext NOT NULL,
	tx_timtab_tb3 text NOT NULL
);

#
# Table structure for table 'tx_timtab_blogroll'
#
CREATE TABLE tx_timtab_blogroll (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	url varchar(255) DEFAULT '' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,
	description varchar(255) DEFAULT '' NOT NULL,
	rel_identity tinyint(3) DEFAULT '0' NOT NULL,
	rel_friendship int(11) DEFAULT '0' NOT NULL,
	rel_physical tinyint(3) DEFAULT '0' NOT NULL,
	rel_professional int(11) DEFAULT '0' NOT NULL,
	rel_geographical int(11) DEFAULT '0' NOT NULL,
	rel_family int(11) DEFAULT '0' NOT NULL,
	rel_romantic int(11) DEFAULT '0' NOT NULL,
	img_uri varchar(255) DEFAULT '' NOT NULL,
	rss_uri varchar(255) DEFAULT '' NOT NULL,
	notes text NOT NULL,
	rating int(11) DEFAULT '0' NOT NULL,
	target int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);