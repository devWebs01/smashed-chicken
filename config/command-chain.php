i<?php

return [
    'chains' => [
        'setup' => [
            'php artisan migrate:fresh',
            'php artisan db:seed',
            'php artisan cache:clear',
            'php artisan config:clear',
            'php artisan storage:link'
        ],
        'deploy' => [
            'php artisan down',
            'php artisan migrate',
            'php artisan up',
        ],
        'test' => [
            // add your test command chain here
        ],
    ],
];
