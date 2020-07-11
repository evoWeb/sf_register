<?php

$EM_CONF['sf_register'] = [
    'title' => 'Frontend User Registration',
    'description' => 'Offers the possibility to maintain the fe_user data in frontend by the user self.',
    'category' => 'fe',
    'author' => 'Sebastian Fischer',
    'author_email' => 'typo3@evoweb.de',
    'author_company' => 'evoWeb',
    'createDirs' => 'typo3temp/sf_register',
    'modify_tables' => 'fe_users',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '9.4.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'suggests' => [
            'extender' => '6.4.7-',
            'recaptcha' => '8.2.7-',
        ],
    ],
];
