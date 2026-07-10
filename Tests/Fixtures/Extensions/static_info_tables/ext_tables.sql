#
# Minimal stub of the static_country_zones table owned by the real static_info_tables
# extension. Only the columns read by StaticCountryZoneRepository::findAllByIso2()
# (SELECT * ... WHERE zn_country_iso_2 = :iso ORDER BY zn_name_local) and rendered by
# SelectStaticCountryZonesViewHelper (optionValueField "uid", optionLabelField
# "zn_name_local") are defined here.
#
CREATE TABLE static_country_zones (
	uid int(11) UNSIGNED NOT NULL auto_increment,
	pid int(11) UNSIGNED DEFAULT '0' NOT NULL,
	zn_country_iso_2 varchar(2) DEFAULT '' NOT NULL,
	zn_code varchar(45) DEFAULT '' NOT NULL,
	zn_name_local varchar(128) DEFAULT '' NOT NULL,
	zn_name_en varchar(50) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid)
);
