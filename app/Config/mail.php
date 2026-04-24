<?php

declare(strict_types=1);

return [
    'from_name' => env('MAIL_FROM_NAME', 'LogiFlow SCM'),
    'from_email' => env('MAIL_FROM_ADDRESS', 'noreply@logiflow.local'),
    'driver' => env('MAIL_DRIVER', 'mail'),
    'smtp' => [
        'host' => env('MAIL_HOST', ''),
        'port' => (int) env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    ],
];

