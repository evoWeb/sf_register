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

#
# Minimal stub of the static_languages table owned by the real static_info_tables
# extension. Queried by StaticLanguageRepository (Extbase ORM: findAll() /
# findByLgCollateLocale() matching "lg_collate_locale") and rendered by
# SelectStaticLanguageViewHelper (optionValueField "lgIso2" -> column lg_iso_2,
# optionLabelField "lgNameEn" -> column lg_name_en). Only those columns are defined
# here; a matching TCA definition (Configuration/TCA/static_languages.php in this stub)
# is required so Extbase can build a DataMap for the Evoweb\SfRegister\Domain\Model\
# StaticLanguage class mapped onto this table (see Configuration/Extbase/Persistence/
# Classes.php in the real extension).
#
CREATE TABLE static_languages (
	uid int(11) UNSIGNED NOT NULL auto_increment,
	pid int(11) UNSIGNED DEFAULT '0' NOT NULL,
	lg_iso_2 varchar(2) DEFAULT '' NOT NULL,
	lg_name_en varchar(50) DEFAULT '' NOT NULL,
	lg_collate_locale varchar(5) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid)
);
