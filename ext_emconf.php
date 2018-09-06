<?php

$EM_CONF['sf_register'] = [
    'title' => 'Frontend User Registration',
    'description' => 'Offers the possibility to maintain the fe_user data in frontend by the user self.',
    'category' => 'plugin',
    'author' => 'Sebastian Fischer',
    'author_email' => 'typo3@evoweb.de',
    'author_company' => 'evoweb',
    'createDirs' => 'typo3temp/sf_register',
    'modify_tables' => 'fe_users',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '9.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.0.0-9.7.99',
        ],
        'suggests' => [
            'extender' => '6.4.7-',
            'recaptcha' => '8.2.7-',
        ],
    ],
];
