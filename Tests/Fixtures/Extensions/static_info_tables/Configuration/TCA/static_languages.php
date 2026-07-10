<?php

declare(strict_types=1);

/*
 * Minimal TCA for the static_languages stub table (see ext_tables.sql in this
 * extension). Extbase requires a TCA schema entry for a table before it will build a
 * DataMap for a mapped domain model (TYPO3\CMS\Extbase\Persistence\Generic\Mapper\
 * DataMapFactory::buildDataMapInternal() checks $tcaSchemaFactory->has($tableName)).
 * Only the columns actually read by StaticLanguageRepository / SelectStaticLanguage-
 * ViewHelper are declared: lg_iso_2 (property lgIso2, optionValueField), lg_name_en
 * (property lgNameEn, optionLabelField) and lg_collate_locale (the raw column name
 * used by findByLgCollateLocale()'s $query->in('lg_collate_locale', ...) filter).
 */
return [
    'ctrl' => [
        'title' => 'static_languages',
        'label' => 'lg_name_en',
    ],
    'columns' => [
        'lg_iso_2' => [
            'label' => 'lg_iso_2',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'max' => 2,
            ],
        ],
        'lg_name_en' => [
            'label' => 'lg_name_en',
            'config' => [
                'type' => 'input',
                'size' => 18,
                'max' => 40,
            ],
        ],
        'lg_collate_locale' => [
            'label' => 'lg_collate_locale',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'max' => 5,
            ],
        ],
    ],
];
