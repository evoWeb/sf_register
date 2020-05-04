<?php

return [
    'frontend' => [
        'sf-register-ajax' => [
            'target' => \Evoweb\SfRegister\Middleware\AjaxMiddleware::class,
            'after' => [
                'typo3/cms-frontend/maintenance-mode'
            ],
        ]
    ]
];
