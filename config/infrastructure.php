<?php

return [
    'shared_hosting' => [
        'compatible' => env('INFRASTRUCTURE_SHARED_HOSTING_COMPATIBLE', true),
        'scheduler' => [
            'driver' => env('INFRASTRUCTURE_SCHEDULER_DRIVER', 'cron'),
        ],
        'workers' => [
            'requires_supervisor' => env('INFRASTRUCTURE_REQUIRES_SUPERVISOR', false),
            'requires_long_running_processes' => env('INFRASTRUCTURE_REQUIRES_LONG_RUNNING_PROCESSES', false),
            'queue_fallback_connection' => env('INFRASTRUCTURE_QUEUE_FALLBACK_CONNECTION', 'sync'),
        ],
        'realtime' => [
            'requires_websockets' => env('INFRASTRUCTURE_REQUIRES_WEBSOCKETS', false),
        ],
        'services' => [
            'requires_redis' => env('INFRASTRUCTURE_REQUIRES_REDIS', false),
        ],
    ],

    'defaults' => [
        'cache_store' => env('CACHE_STORE', 'database'),
        'queue_connection' => env('QUEUE_CONNECTION', 'database'),
        'session_driver' => env('SESSION_DRIVER', 'database'),
        'mail_mailer' => env('MAIL_MAILER', 'log'),
        'broadcast_connection' => env('BROADCAST_CONNECTION', 'log'),
    ],
];
