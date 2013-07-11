#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	mailhash varchar(60) DEFAULT '',
	activated_on int(11) unsigned DEFAULT '0' NOT NULL,

	pseudonym varchar(50) DEFAULT '',
	gender int(11) unsigned DEFAULT '0' NOT NULL,
	date_of_birth int(11) DEFAULT '0' NOT NULL,
	language char(2) DEFAULT '' NOT NULL,
	zone varchar(45) DEFAULT '' NOT NULL,
	static_info_country char(3) DEFAULT '' NOT NULL,
	timezone float DEFAULT '0' NOT NULL,
	daylight tinyint(4) unsigned DEFAULT '0' NOT NULL,
	mobilephone varchar(20) DEFAULT '' NOT NULL,
	gtc tinyint(4) unsigned DEFAULT '0' NOT NULL,
	privacy tinyint(4) unsigned DEFAULT '0' NOT NULL,
	status int(11) unsigned DEFAULT '0' NOT NULL,
	by_invitation tinyint(4) unsigned DEFAULT '0' NOT NULL,
	comments text NOT NULL,
	module_sys_dmail_html tinyint(3) unsigned DEFAULT '0' NOT NULL
	module_sys_dmail_category int(10) unsigned DEFAULT '0' NOT NULL,
	email_new varchar(80) DEFAULT '' NOT NULL,

	custom0 varchar(1) DEFAULT '',
	custom1 varchar(1) DEFAULT '',
	custom2 varchar(1) DEFAULT '',
	custom3 varchar(1) DEFAULT '',
	custom4 varchar(1) DEFAULT '',
	custom5 varchar(1) DEFAULT '',
	custom6 varchar(1) DEFAULT '',
	custom7 varchar(1) DEFAULT '',
	custom8 varchar(1) DEFAULT '',
	custom9 varchar(1) DEFAULT '',
);
