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
    'version' => '8.8.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.7.99',
        ],
        'suggests' => [
            'extender' => '6.3.0-',
            'recaptcha' => '7.2.1-',
        ],
    ],
];
