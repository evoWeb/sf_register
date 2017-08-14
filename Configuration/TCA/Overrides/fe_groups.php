<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('felogin')) {
        // Adds the redirect field to the fe_groups table
        $additionalColumns = [
            'felogin_redirectPid' => [
                'exclude' => true,
                'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:felogin_redirectPid',
                'config' => [
                    'type' => 'group',
                    'internal_type' => 'db',
                    'allowed' => 'pages',
                    'size' => 1,
                    'minitems' => 0,
                    'maxitems' => 1,
                ]
            ]
        ];

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups', $additionalColumns);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'fe_groups',
            'felogin_redirectPid',
            '',
            'after:TSconfig'
        );
    }
});
