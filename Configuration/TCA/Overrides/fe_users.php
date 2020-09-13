<?php

defined('TYPO3_MODE') || die();

call_user_func(function () {
    $languageFile = 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:';

    $temporaryColumns = [
        'activated_on' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.activated_on',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'readOnly' => true,
                'eval' => 'datetime',
            ]
        ],

        'pseudonym' => [
            'label' => $languageFile . 'fe_users.pseudonym',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 50,
                'eval' => 'trim',
            ]
        ],
        'gender' => [
            'label' => $languageFile . 'fe_users.gender',
            'config' => [
                'type' => 'radio',
                'items' => [
                    [$languageFile . 'fe_users.gender.I.1', '1'],
                    [$languageFile . 'fe_users.gender.I.2', '2']
                ],
            ]
        ],
        'date_of_birth' => [
            'label' => $languageFile . 'fe_users.date_of_birth',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 10,
                'eval' => 'date',
                'default' => 0
            ]
        ],
        'language' => [
            'label' => $languageFile . 'fe_users.language',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'max' => 2,
                'eval' => '',
                'default' => ''
            ]
        ],
        'zone' => [
            'label' => $languageFile . 'fe_users.zone',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 40,
                'eval' => 'trim',
                'default' => ''
            ]
        ],
        'timezone' => [
            'label' => $languageFile . 'fe_users.timezone',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$languageFile . 'fe_users.timezone.I.-12', -12],
                    [$languageFile . 'fe_users.timezone.I.-11', -11],
                    [$languageFile . 'fe_users.timezone.I.-10', -10],
                    [$languageFile . 'fe_users.timezone.I.-9.5', -9.5],
                    [$languageFile . 'fe_users.timezone.I.-9', -9],
                    [$languageFile . 'fe_users.timezone.I.-8', -8],
                    [$languageFile . 'fe_users.timezone.I.-7', -7],
                    [$languageFile . 'fe_users.timezone.I.-6', -6],
                    [$languageFile . 'fe_users.timezone.I.-5', -5],
                    [$languageFile . 'fe_users.timezone.I.-4.5', -4.5],
                    [$languageFile . 'fe_users.timezone.I.-4', -4],
                    [$languageFile . 'fe_users.timezone.I.-3.5', -3.5],
                    [$languageFile . 'fe_users.timezone.I.-3', -3],
                    [$languageFile . 'fe_users.timezone.I.-2', -2],
                    [$languageFile . 'fe_users.timezone.I.-1', -1],
                    [$languageFile . 'fe_users.timezone.I.0', 0],
                    [$languageFile . 'fe_users.timezone.I.1', 1],
                    [$languageFile . 'fe_users.timezone.I.2', 2],
                    [$languageFile . 'fe_users.timezone.I.3', 3],
                    [$languageFile . 'fe_users.timezone.I.3.5', 3.5],
                    [$languageFile . 'fe_users.timezone.I.4', 4],
                    [$languageFile . 'fe_users.timezone.I.4.5', 4.5],
                    [$languageFile . 'fe_users.timezone.I.5', 5],
                    [$languageFile . 'fe_users.timezone.I.5.5', 5.5],
                    [$languageFile . 'fe_users.timezone.I.5.75', 5.75],
                    [$languageFile . 'fe_users.timezone.I.6', 6],
                    [$languageFile . 'fe_users.timezone.I.6.5', 6.5],
                    [$languageFile . 'fe_users.timezone.I.7', 7],
                    [$languageFile . 'fe_users.timezone.I.8', 8],
                    [$languageFile . 'fe_users.timezone.I.9', 9],
                    [$languageFile . 'fe_users.timezone.I.9.5', 9.5],
                    [$languageFile . 'fe_users.timezone.I.10', 10],
                    [$languageFile . 'fe_users.timezone.I.11', 11],
                    [$languageFile . 'fe_users.timezone.I.11.5', 11.5],
                    [$languageFile . 'fe_users.timezone.I.12', 12],
                    [$languageFile . 'fe_users.timezone.I.12.75', 12.75],
                    [$languageFile . 'fe_users.timezone.I.13', 13],
                    [$languageFile . 'fe_users.timezone.I.14', 14],
                ],
                'default' => 0,
            ]
        ],
        'daylight' => [
            'label' => $languageFile . 'fe_users.daylight',
            'config' => [
                'type' => 'check'
            ]
        ],
        'mobilephone' => [
            'label' => $languageFile . 'fe_users.mobilephone',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ]
        ],
        'gtc' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.gtc',
            'config' => [
                'type' => 'check'
            ]
        ],
        'privacy' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.privacy',
            'config' => [
                'type' => 'check'
            ]
        ],
        'status' => [
            'label' => $languageFile . 'fe_users.status',
            'config' => [
                'type' => 'check',
                'renderType' => 'selectSingle',
                'items' => [
                    [$languageFile . 'fe_users.status.I.0', 0],
                    [$languageFile . 'fe_users.status.I.1', 1],
                    [$languageFile . 'fe_users.status.I.2', 2],
                    [$languageFile . 'fe_users.status.I.3', 3],
                    [$languageFile . 'fe_users.status.I.4', 4],
                ],
            ]
        ],
        'by_invitation' => [
            'label' => $languageFile . 'fe_users.by_invitation',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'comments' => [
            'label' => $languageFile . 'fe_users.comments',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 48,
                'default' => '',
            ]
        ],
        'email_new' => [
            'label' => $languageFile . 'fe_users.email_new',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 20,
                'eval' => 'trim',
            ]
        ],
        'invitation_email' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'module_sys_dmail_newsletter' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.module_sys_dmail_newsletter',
            'config' => [
                'type' => 'check'
            ]
        ],
        'module_sys_dmail_html' => [
            'exclude' => true,
            'label' => $languageFile . 'fe_users.module_sys_dmail_html',
            'config' => [
                'type' => 'check'
            ]
        ],
        'image' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'maxitems' => 1,
                    'minitems' => 0
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'gender', '', 'before:name');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'pseudonym', '', 'after:username');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'email_new', '', 'after:email');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'date_of_birth, language, status',
        '',
        'after:name'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'zone, timezone, daylight',
        '',
        'after:city'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'mobilephone',
        '',
        'after:telephone'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        '--div--;LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:fe_users.div.registration,
            activated_on, gtc, privacy, by_invitation, comments, module_sys_dmail_newsletter, module_sys_dmail_html'
    );

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
        $tempColumns = [
            'static_info_country' => [
                'exclude' => 0,
                'label' => $languageFile . 'fe_users.static_info_country',
                'config' => [
                    'type' => 'input',
                    'size' => '5',
                    'max' => '3',
                    'eval' => '',
                    'default' => ''
                ]
            ],
        ];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'fe_users',
            'static_info_country',
            '',
            'after:zone'
        );
    }
});
