<?php

return [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'options' => [
        'timeout' => 30,
        'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    ],
];