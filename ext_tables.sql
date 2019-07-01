#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
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
	comments text,
	module_sys_dmail_newsletter tinyint(3) unsigned DEFAULT '0' NOT NULL,
	module_sys_dmail_html tinyint(3) unsigned DEFAULT '0' NOT NULL,
	module_sys_dmail_category text,
	email_new varchar(254) DEFAULT '' NOT NULL,
	invitation_email varchar(254) DEFAULT '' NOT NULL
);


#
# Table structure for table 'fe_groups'
#
CREATE TABLE fe_groups (
	felogin_redirectPid tinytext
);
