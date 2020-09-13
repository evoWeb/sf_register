<?php

declare(strict_types=1);

return [
    \Evoweb\SfRegister\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \Evoweb\SfRegister\Domain\Model\FrontendUserGroup::class => [
        'tableName' => 'fe_groups',
        'properties' => [
            'feloginRedirectPid' => [
                'fieldName' => 'felogin_redirectPid'
            ],
        ],
    ],
    \Evoweb\SfRegister\Domain\Model\StaticCountry::class => [
        'tableName' => 'static_countries',
    ],
    \Evoweb\SfRegister\Domain\Model\StaticCountryZone::class => [
        'tableName' => 'static_country_zones',
    ],
    \Evoweb\SfRegister\Domain\Model\StaticLanguage::class => [
        'tableName' => 'static_languages',
    ],
];
