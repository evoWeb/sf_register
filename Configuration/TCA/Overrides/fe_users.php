<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function () {
    $languageFile = 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:';

    $temporaryColumns = [
        'activated_on' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.activated_on',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'format' => 'datetime',
                'eval' => 'int',
            ],
        ],
        'pseudonym' => [
            'label' => $languageFile . 'fe_users.pseudonym',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 50,
                'eval' => 'trim',
            ],
        ],
        'gender' => [
            'label' => $languageFile . 'fe_users.gender',
            'config' => [
                'type' => 'radio',
                'items' => [
                    ['label' => $languageFile . 'fe_users.gender.I.1', 'value' => 1],
                    ['label' => $languageFile . 'fe_users.gender.I.2', 'value' => 2],
                ],
            ],
        ],
        'date_of_birth' => [
            'label' => $languageFile . 'fe_users.date_of_birth',
            'config' => [
                'type' => 'datetime',
                'size' => 10,
                'format' => 'date',
                'eval' => 'int',
                'default' => 0,
            ],
        ],
        'language' => [
            'label' => $languageFile . 'fe_users.language',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'max' => 2,
                'eval' => '',
                'default' => '',
            ],
        ],
        'zone' => [
            'label' => $languageFile . 'fe_users.zone',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 40,
                'eval' => 'trim',
                'default' => '',
            ],
        ],
        'timezone' => [
            'label' => $languageFile . 'fe_users.timezone',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $languageFile . 'fe_users.timezone.I.-12', 'value' => -12],
                    ['label' => $languageFile . 'fe_users.timezone.I.-11', 'value' => -11],
                    ['label' => $languageFile . 'fe_users.timezone.I.-10', 'value' => -10],
                    ['label' => $languageFile . 'fe_users.timezone.I.-9', 'value' => -9],
                    ['label' => $languageFile . 'fe_users.timezone.I.-8', 'value' => -8],
                    ['label' => $languageFile . 'fe_users.timezone.I.-7', 'value' => -7],
                    ['label' => $languageFile . 'fe_users.timezone.I.-6', 'value' => -6],
                    ['label' => $languageFile . 'fe_users.timezone.I.-5', 'value' => -5],
                    ['label' => $languageFile . 'fe_users.timezone.I.-4', 'value' => -4],
                    ['label' => $languageFile . 'fe_users.timezone.I.-3', 'value' => -3],
                    ['label' => $languageFile . 'fe_users.timezone.I.-2', 'value' => -2],
                    ['label' => $languageFile . 'fe_users.timezone.I.-1', 'value' => -1],
                    ['label' => $languageFile . 'fe_users.timezone.I.0', 'value' => 0],
                    ['label' => $languageFile . 'fe_users.timezone.I.1', 'value' => 1],
                    ['label' => $languageFile . 'fe_users.timezone.I.2', 'value' => 2],
                    ['label' => $languageFile . 'fe_users.timezone.I.3', 'value' => 3],
                    ['label' => $languageFile . 'fe_users.timezone.I.4', 'value' => 4],
                    ['label' => $languageFile . 'fe_users.timezone.I.5', 'value' => 5],
                    ['label' => $languageFile . 'fe_users.timezone.I.6', 'value' => 6],
                    ['label' => $languageFile . 'fe_users.timezone.I.7', 'value' => 7],
                    ['label' => $languageFile . 'fe_users.timezone.I.8', 'value' => 8],
                    ['label' => $languageFile . 'fe_users.timezone.I.9', 'value' => 9],
                    ['label' => $languageFile . 'fe_users.timezone.I.10', 'value' => 10],
                    ['label' => $languageFile . 'fe_users.timezone.I.11', 'value' => 11],
                    ['label' => $languageFile . 'fe_users.timezone.I.12', 'value' => 12],
                    ['label' => $languageFile . 'fe_users.timezone.I.13', 'value' => 13],
                    ['label' => $languageFile . 'fe_users.timezone.I.14', 'value' => 14],
                ],
                'default' => 0,
            ],
        ],
        'daylight' => [
            'label' => $languageFile . 'fe_users.daylight',
            'config' => [
                'type' => 'check',
            ],
        ],
        'mobilephone' => [
            'label' => $languageFile . 'fe_users.mobilephone',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ],
        ],
        'gtc' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.gtc',
            'config' => [
                'type' => 'check',
            ],
        ],
        'privacy' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.privacy',
            'config' => [
                'type' => 'check',
            ],
        ],
        'status' => [
            'label' => $languageFile . 'fe_users.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $languageFile . 'fe_users.status.I.0', 'value' => 0],
                    ['label' => $languageFile . 'fe_users.status.I.1', 'value' => 1],
                    ['label' => $languageFile . 'fe_users.status.I.2', 'value' => 2],
                    ['label' => $languageFile . 'fe_users.status.I.3', 'value' => 3],
                    ['label' => $languageFile . 'fe_users.status.I.4', 'value' => 4],
                ],
            ],
        ],
        'by_invitation' => [
            'label' => $languageFile . 'fe_users.by_invitation',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'comments' => [
            'label' => $languageFile . 'fe_users.comments',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 48,
                'default' => '',
            ],
        ],
        'email_new' => [
            'label' => $languageFile . 'fe_users.email_new',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 20,
                'eval' => 'trim',
            ],
        ],
        'invitation_email' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'module_sys_dmail_newsletter' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.module_sys_dmail_newsletter',
            'config' => [
                'type' => 'check',
            ],
        ],
        'module_sys_dmail_html' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.module_sys_dmail_html',
            'config' => [
                'type' => 'check',
            ],
        ],
        'image' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.image',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
    ];

    ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns);
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'gender', '', 'before:name');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'pseudonym', '', 'after:username');
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'email_new', '', 'after:email');
    ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'date_of_birth, language, status',
        '',
        'after:name'
    );
    ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'zone, timezone, daylight',
        '',
        'after:city'
    );
    ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'mobilephone',
        '',
        'after:telephone'
    );
    ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        '--div--;LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:fe_users.div.registration,
            activated_on, gtc, privacy, by_invitation, comments, module_sys_dmail_newsletter, module_sys_dmail_html'
    );

    if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
        $tempColumns = [
            'static_info_country' => [
                'exclude' => 0,
                'label' => $languageFile . 'fe_users.static_info_country',
                'config' => [
                    'type' => 'input',
                    'size' => '5',
                    'max' => '3',
                    'eval' => '',
                    'default' => '',
                ],
            ],
        ];
        ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
        ExtensionManagementUtility::addToAllTCAtypes(
            'fe_users',
            'static_info_country',
            '',
            'after:zone'
        );
    }
})();
