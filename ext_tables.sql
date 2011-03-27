#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	mailhash varchar(60) DEFAULT '',

	mobilephone varchar(20) DEFAULT '' NOT NULL,
	gtc tinyint(4) unsigned DEFAULT '0' NOT NULL,
);
