<?php

return [
    'chains' => [
        'setup' => [
            'cp .env.example .env',
            'php artisan key:generate',
            'php artisan migrate:fresh',
            'php artisan db:seed',
            'php artisan cache:clear',
            'php artisan config:clear',
            'php artisan storage:link',
            'php artisan config:cache',
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
