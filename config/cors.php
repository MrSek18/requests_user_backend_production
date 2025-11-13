<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'company_representatives/*',
        'add_request',
        'login',
        'logout',
        'user'
    ],

    'allowed_methods' => ['*'],

    'supports_credentials' => true,

    'allowed_origins' => [
        'http://localhost:3000',
        'https://requests-user-frontend-production.vercel.app'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,
];
