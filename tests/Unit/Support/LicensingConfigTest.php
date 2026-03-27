<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicensingConfigTest extends TestCase
{
    #[Test]
    public function it_exposes_shared_licensing_defaults(): void
    {
        $this->assertSame(1, config('licensing.devices.default_max_devices'));
        $this->assertSame(3600, config('licensing.devices.heartbeat_interval_seconds'));
        $this->assertSame(3600, config('licensing.devices.stale_threshold_seconds'));
        $this->assertSame(3, config('licensing.devices.heartbeat_retention_days'));
        $this->assertSame(300, config('licensing.device_mismatch.grace_period_seconds'));
        $this->assertSame('device_mismatch', config('licensing.device_mismatch.block_reason_code'));
    }

    #[Test]
    public function it_reads_licensing_values_from_environment_variables(): void
    {
        $original = [
            'LICENSING_DEFAULT_MAX_DEVICES' => getenv('LICENSING_DEFAULT_MAX_DEVICES') ?: null,
            'LICENSING_HEARTBEAT_INTERVAL_SECONDS' => getenv('LICENSING_HEARTBEAT_INTERVAL_SECONDS') ?: null,
            'LICENSING_STALE_DEVICE_THRESHOLD_SECONDS' => getenv('LICENSING_STALE_DEVICE_THRESHOLD_SECONDS') ?: null,
            'LICENSING_HEARTBEAT_RETENTION_DAYS' => getenv('LICENSING_HEARTBEAT_RETENTION_DAYS') ?: null,
            'LICENSING_DEVICE_MISMATCH_GRACE_PERIOD_SECONDS' => getenv('LICENSING_DEVICE_MISMATCH_GRACE_PERIOD_SECONDS') ?: null,
            'LICENSING_DEVICE_MISMATCH_BLOCK_REASON_CODE' => getenv('LICENSING_DEVICE_MISMATCH_BLOCK_REASON_CODE') ?: null,
        ];

        putenv('LICENSING_DEFAULT_MAX_DEVICES=4');
        putenv('LICENSING_HEARTBEAT_INTERVAL_SECONDS=7200');
        putenv('LICENSING_STALE_DEVICE_THRESHOLD_SECONDS=5400');
        putenv('LICENSING_HEARTBEAT_RETENTION_DAYS=7');
        putenv('LICENSING_DEVICE_MISMATCH_GRACE_PERIOD_SECONDS=900');
        putenv('LICENSING_DEVICE_MISMATCH_BLOCK_REASON_CODE=custom_mismatch');

        $_ENV['LICENSING_DEFAULT_MAX_DEVICES'] = '4';
        $_ENV['LICENSING_HEARTBEAT_INTERVAL_SECONDS'] = '7200';
        $_ENV['LICENSING_STALE_DEVICE_THRESHOLD_SECONDS'] = '5400';
        $_ENV['LICENSING_HEARTBEAT_RETENTION_DAYS'] = '7';
        $_ENV['LICENSING_DEVICE_MISMATCH_GRACE_PERIOD_SECONDS'] = '900';
        $_ENV['LICENSING_DEVICE_MISMATCH_BLOCK_REASON_CODE'] = 'custom_mismatch';

        $_SERVER['LICENSING_DEFAULT_MAX_DEVICES'] = '4';
        $_SERVER['LICENSING_HEARTBEAT_INTERVAL_SECONDS'] = '7200';
        $_SERVER['LICENSING_STALE_DEVICE_THRESHOLD_SECONDS'] = '5400';
        $_SERVER['LICENSING_HEARTBEAT_RETENTION_DAYS'] = '7';
        $_SERVER['LICENSING_DEVICE_MISMATCH_GRACE_PERIOD_SECONDS'] = '900';
        $_SERVER['LICENSING_DEVICE_MISMATCH_BLOCK_REASON_CODE'] = 'custom_mismatch';

        $config = require config_path('licensing.php');

        $this->assertSame(4, $config['devices']['default_max_devices']);
        $this->assertSame(7200, $config['devices']['heartbeat_interval_seconds']);
        $this->assertSame(5400, $config['devices']['stale_threshold_seconds']);
        $this->assertSame(7, $config['devices']['heartbeat_retention_days']);
        $this->assertSame(900, $config['device_mismatch']['grace_period_seconds']);
        $this->assertSame('custom_mismatch', $config['device_mismatch']['block_reason_code']);

        foreach ($original as $key => $value) {
            if ($value === null) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);

                continue;
            }

            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
