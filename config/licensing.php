<?php

return [
    'identifiers' => [
        'license_key_prefix' => env('LICENSING_LICENSE_KEY_PREFIX', 'XARGO'),
        'public_key_prefix' => env('LICENSING_PUBLIC_KEY_PREFIX', 'lic_'),
    ],

    'devices' => [
        'default_max_devices' => (int) env('LICENSING_DEFAULT_MAX_DEVICES', 1),
        'heartbeat_interval_seconds' => (int) env('LICENSING_HEARTBEAT_INTERVAL_SECONDS', 3600),
        'stale_threshold_seconds' => (int) env('LICENSING_STALE_DEVICE_THRESHOLD_SECONDS', 3600),
        'heartbeat_retention_days' => (int) env('LICENSING_HEARTBEAT_RETENTION_DAYS', 3),
    ],

    'device_mismatch' => [
        'grace_period_seconds' => (int) env('LICENSING_DEVICE_MISMATCH_GRACE_PERIOD_SECONDS', 300),
        'block_reason_code' => env('LICENSING_DEVICE_MISMATCH_BLOCK_REASON_CODE', 'device_mismatch'),
    ],

    'api' => [
        'rate_limit_per_minute' => (int) env('LICENSING_API_RATE_LIMIT_PER_MINUTE', 120),
    ],

    'notifications' => [
        'expiry_warning_days' => (int) env('LICENSING_EXPIRY_WARNING_DAYS', 7),
        'trial_ending_warning_days' => (int) env('LICENSING_TRIAL_ENDING_WARNING_DAYS', 3),
        'device_mismatch_alerts_enabled' => filter_var(
            env('LICENSING_DEVICE_MISMATCH_ALERTS_ENABLED', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE,
        ) ?? true,
        'rebind_notifications_enabled' => filter_var(
            env('LICENSING_REBIND_NOTIFICATIONS_ENABLED', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE,
        ) ?? true,
    ],
];
