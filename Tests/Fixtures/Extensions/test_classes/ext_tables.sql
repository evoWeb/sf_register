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
