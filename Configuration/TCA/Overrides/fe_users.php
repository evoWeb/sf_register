<?php
defined('TYPO3_MODE') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$temporaryColumns = array(
    'mailhash' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.mailhash',
        'config' => array(
            'type' => 'input',
            'readOnly' => true,
        )
    ),

    'activated_on' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.activated_on',
        'config' => array(
            'type' => 'input',
            'readOnly' => true,
            'eval' => 'datetime',
        )
    ),

    'pseudonym' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.pseudonym',
        'config' => array(
            'type' => 'input',
            'size' => '20',
            'max' => '50',
            'eval' => 'trim',
        )
    ),
    'gender' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.gender',
        'config' => array(
            'type' => 'radio',
            'items' => array(
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.gender.I.1', '1'),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.gender.I.2', '2')
            ),
        )
    ),
    'date_of_birth' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.date_of_birth',
        'config' => array(
            'type' => 'input',
            'size' => '10',
            'max' => '20',
            'eval' => 'date',
            'checkbox' => '0',
            'default' => ''
        )
    ),
    'language' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.language',
        'config' => array(
            'type' => 'input',
            'size' => '4',
            'max' => '2',
            'eval' => '',
            'default' => ''
        )
    ),
    'zone' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.zone',
        'config' => array(
            'type' => 'input',
            'size' => '20',
            'max' => '40',
            'eval' => 'trim',
            'default' => ''
        )
    ),
    'timezone' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone',
        'config' => array(
            'type' => 'select',
            'items' => array(
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-12', -12),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-11', -11),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-10', -10),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-9.5', -9.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-9', -9),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-8', -8),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-7', -7),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-6', -6),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-5', -5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-4.5', -4.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-4', -4),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-3.5', -3.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-3', -3),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-2', -2),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-1', -1),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.0', 0),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.1', 1),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.2', 2),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.3', 3),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.3.5', 3.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.4', 4),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.4.5', 4.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.5', 5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.5.5', 5.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.5.75', 5.75),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.6', 6),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.6.5', 6.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.7', 7),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.8', 8),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.9', 9),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.9.5', 9.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.10', 10),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.11', 11),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.11.5', 11.5),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.12', 12),
                array(
                    'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.12.75',
                    12.75
                ),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.13', 13),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.14', 14),
            ),
            'size' => 1,
            'maxitems' => 1,
            'default' => 0,
        )
    ),
    'daylight' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.daylight',
        'config' => array(
            'type' => 'check'
        )
    ),
    'mobilephone' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.mobilephone',
        'config' => array(
            'type' => 'input',
            'size' => 20,
        )
    ),
    'gtc' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.gtc',
        'config' => array(
            'type' => 'check'
        )
    ),
    'privacy' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.privacy',
        'config' => array(
            'type' => 'check'
        )
    ),
    'status' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status',
        'config' => array(
            'type' => 'select',
            'items' => array(
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.0', '0'),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.1', '1'),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.2', '2'),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.3', '3'),
                array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.4', '4'),
            ),
            'size' => 1,
            'maxitems' => 1,
        )
    ),
    'by_invitation' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.by_invitation',
        'config' => array(
            'type' => 'check',
            'default' => '0'
        )
    ),
    'comments' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.comments',
        'config' => array(
            'type' => 'text',
            'rows' => '5',
            'cols' => '48'
        )
    ),
    'email_new' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.email_new',
        'config' => array(
            'type' => 'input',
            'size' => '20',
            'max' => '80',
            'eval' => 'trim',
        )
    ),
    'module_sys_dmail_newsletter' => array(
        'label' =>
            'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.module_sys_dmail_newsletter',
        'exclude' => '1',
        'config' => array(
            'type' => 'check'
        )
    ),
    'module_sys_dmail_html' => array(
        'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.module_sys_dmail_html',
        'exclude' => '1',
        'config' => array(
            'type' => 'check'
        )
    ),
    'image' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.image',
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            'image',
            array(
                'type' => 'inline',
                'appearance' => array(
                    'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
                ),
                'foreign_match_fields' => array(
                    'fieldname' => 'image',
                    'tablenames' => 'fe_users',
                    'table_local' => 'sys_file',
                ),
            ),
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
        ),
    ),
);

ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns);
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'gender', '', 'before:name');
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'pseudonym', '', 'after:username');
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'date_of_birth, language, status', '', 'after:name');
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'zone, timezone, daylight', '', 'after:city');
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'mobilephone', '', 'after:telephone');
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'email_new', '', 'after:email');
ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.div.registration,
    mailhash, activated_on, gtc, privacy, by_invitation, comments, module_sys_dmail_html'
);

if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
    $tempColumns = array(
        'static_info_country' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.static_info_country',
            'config' => array(
                'type' => 'input',
                'size' => '5',
                'max' => '3',
                'eval' => '',
                'default' => ''
            )
        ),
    );
    ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
    ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'static_info_country', '', 'after:zone');
}
