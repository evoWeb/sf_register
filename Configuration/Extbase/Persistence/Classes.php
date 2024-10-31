<?php

declare(strict_types=1);

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\FrontendUserGroup;
use Evoweb\SfRegister\Domain\Model\StaticCountry;
use Evoweb\SfRegister\Domain\Model\StaticCountryZone;
use Evoweb\SfRegister\Domain\Model\StaticLanguage;

return [
    FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    FrontendUserGroup::class => [
        'tableName' => 'fe_groups',
        'properties' => [
            'feloginRedirectPid' => [
                'fieldName' => 'felogin_redirectPid',
            ],
        ],
    ],
    StaticCountry::class => [
        'tableName' => 'static_countries',
    ],
    StaticCountryZone::class => [
        'tableName' => 'static_country_zones',
    ],
    StaticLanguage::class => [
        'tableName' => 'static_languages',
    ],
];
