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
    'version' => '10.1.5',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
        ],
        'suggests' => [
            'extender' => '7.0.0-',
            'recaptcha' => '10.0.0-',
        ],
    ],
];
