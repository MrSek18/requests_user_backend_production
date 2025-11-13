<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'company_representatives/*', 'add_request', 'login', 'logout', 'user'],

    'allowed_methods' => ['*'],

    'supports_credentials' => true,

    'allowed_origins' => [
        'http://localhost:3000',
        'https://mi-frontend.loca.lt',
        'https://red-donkey-35.loca.lt',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,


];
