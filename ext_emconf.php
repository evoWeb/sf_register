<?php

$EM_CONF['sf_register'] = [
    'title' => 'Frontend User Registration',
    'description' => 'Offers the possibility to maintain the fe_user data in frontend by the user self.',
    'version' => '8.7.0',
    'category' => 'plugin',
    'state' => 'stable',
    'author' => 'Sebastian Fischer',
    'author_email' => 'typo3@evoweb.de',
    'author_company' => 'evoweb',
    'createDirs' => 'typo3temp/sf_register',
    'modify_tables' => 'fe_users',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
        ],
        'suggests' => [
            'extender' => '6.3.0-',
        ],
    ],
];
