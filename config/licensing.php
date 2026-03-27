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
];
