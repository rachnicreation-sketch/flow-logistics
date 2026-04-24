<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'LogiFlow SCM'),
    'env' => env('APP_ENV', 'local'),
    'debug' => filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOL),
    'base_url' => env('APP_URL', '/'),
    'session_key' => 'flow_logistics_session',
];

