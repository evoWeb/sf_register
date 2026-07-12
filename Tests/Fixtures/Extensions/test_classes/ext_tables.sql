#
# SwitchableControllerActionsPluginUpdater targets tt_content.list_type, a legacy column that
# real upgrade databases still carry over from pre-CType-only sf_register/TYPO3 installs, even
# though the TCA of the currently loaded core (14/15) no longer declares it. The test schema
# needs this column added back so the wizard's raw QueryBuilder (uid, list_type, pi_flexform)
# can be exercised against fixture data the same way it would run against a real upgrade DB.
#
CREATE TABLE tt_content (
	list_type varchar(255) DEFAULT '' NOT NULL
);

#
# UserCountryMigration reads/writes fe_users.static_info_country, a legacy column added via
# Configuration/TCA/Overrides/fe_users.php (addTCAcolumns) for pre-v13 installs that stored the
# user's country as a static_info_tables uid there. No ext_tables.sql in the currently loaded
# extension declares this column (TCA-only metadata does not create a DB column), yet real
# upgrade databases still carry it over from those older installs. The test schema needs it
# added back so the wizard's raw QueryBuilder (uid, static_info_country) can be exercised
# against fixture data the same way it would run against a real upgrade DB.
#
CREATE TABLE fe_users (
	static_info_country varchar(3) DEFAULT '' NOT NULL
);
