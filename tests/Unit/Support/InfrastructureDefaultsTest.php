<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InfrastructureDefaultsTest extends TestCase
{
    #[Test]
    public function it_defaults_to_shared_hosting_friendly_infrastructure(): void
    {
        $this->assertTrue(config('infrastructure.shared_hosting.compatible'));
        $this->assertSame('cron', config('infrastructure.shared_hosting.scheduler.driver'));
        $this->assertFalse(config('infrastructure.shared_hosting.workers.requires_supervisor'));
        $this->assertFalse(config('infrastructure.shared_hosting.workers.requires_long_running_processes'));
        $this->assertSame('sync', config('infrastructure.shared_hosting.workers.queue_fallback_connection'));
        $this->assertFalse(config('infrastructure.shared_hosting.realtime.requires_websockets'));
        $this->assertFalse(config('infrastructure.shared_hosting.services.requires_redis'));
        $this->assertContains(config('infrastructure.defaults.cache_store'), ['database', 'array', 'file']);
        $this->assertContains(config('infrastructure.defaults.queue_connection'), ['database', 'sync']);
        $this->assertContains(config('infrastructure.defaults.session_driver'), ['database', 'array', 'file']);
        $this->assertContains(config('infrastructure.defaults.mail_mailer'), ['log', 'array']);
        $this->assertContains(config('infrastructure.defaults.broadcast_connection'), ['log', null]);
    }

    #[Test]
    public function it_allows_infrastructure_defaults_to_be_overridden_from_environment(): void
    {
        $original = [
            'INFRASTRUCTURE_SHARED_HOSTING_COMPATIBLE' => getenv('INFRASTRUCTURE_SHARED_HOSTING_COMPATIBLE') ?: null,
            'INFRASTRUCTURE_SCHEDULER_DRIVER' => getenv('INFRASTRUCTURE_SCHEDULER_DRIVER') ?: null,
            'INFRASTRUCTURE_REQUIRES_SUPERVISOR' => getenv('INFRASTRUCTURE_REQUIRES_SUPERVISOR') ?: null,
            'INFRASTRUCTURE_REQUIRES_LONG_RUNNING_PROCESSES' => getenv('INFRASTRUCTURE_REQUIRES_LONG_RUNNING_PROCESSES') ?: null,
            'INFRASTRUCTURE_QUEUE_FALLBACK_CONNECTION' => getenv('INFRASTRUCTURE_QUEUE_FALLBACK_CONNECTION') ?: null,
            'INFRASTRUCTURE_REQUIRES_WEBSOCKETS' => getenv('INFRASTRUCTURE_REQUIRES_WEBSOCKETS') ?: null,
            'INFRASTRUCTURE_REQUIRES_REDIS' => getenv('INFRASTRUCTURE_REQUIRES_REDIS') ?: null,
            'CACHE_STORE' => getenv('CACHE_STORE') ?: null,
            'QUEUE_CONNECTION' => getenv('QUEUE_CONNECTION') ?: null,
            'SESSION_DRIVER' => getenv('SESSION_DRIVER') ?: null,
            'MAIL_MAILER' => getenv('MAIL_MAILER') ?: null,
            'BROADCAST_CONNECTION' => getenv('BROADCAST_CONNECTION') ?: null,
        ];

        putenv('INFRASTRUCTURE_SHARED_HOSTING_COMPATIBLE=false');
        putenv('INFRASTRUCTURE_SCHEDULER_DRIVER=manual');
        putenv('INFRASTRUCTURE_REQUIRES_SUPERVISOR=true');
        putenv('INFRASTRUCTURE_REQUIRES_LONG_RUNNING_PROCESSES=true');
        putenv('INFRASTRUCTURE_QUEUE_FALLBACK_CONNECTION=database');
        putenv('INFRASTRUCTURE_REQUIRES_WEBSOCKETS=true');
        putenv('INFRASTRUCTURE_REQUIRES_REDIS=true');
        putenv('CACHE_STORE=file');
        putenv('QUEUE_CONNECTION=sync');
        putenv('SESSION_DRIVER=file');
        putenv('MAIL_MAILER=array');
        putenv('BROADCAST_CONNECTION=null');

        $_ENV['INFRASTRUCTURE_SHARED_HOSTING_COMPATIBLE'] = 'false';
        $_ENV['INFRASTRUCTURE_SCHEDULER_DRIVER'] = 'manual';
        $_ENV['INFRASTRUCTURE_REQUIRES_SUPERVISOR'] = 'true';
        $_ENV['INFRASTRUCTURE_REQUIRES_LONG_RUNNING_PROCESSES'] = 'true';
        $_ENV['INFRASTRUCTURE_QUEUE_FALLBACK_CONNECTION'] = 'database';
        $_ENV['INFRASTRUCTURE_REQUIRES_WEBSOCKETS'] = 'true';
        $_ENV['INFRASTRUCTURE_REQUIRES_REDIS'] = 'true';
        $_ENV['CACHE_STORE'] = 'file';
        $_ENV['QUEUE_CONNECTION'] = 'sync';
        $_ENV['SESSION_DRIVER'] = 'file';
        $_ENV['MAIL_MAILER'] = 'array';
        $_ENV['BROADCAST_CONNECTION'] = 'null';

        $_SERVER['INFRASTRUCTURE_SHARED_HOSTING_COMPATIBLE'] = 'false';
        $_SERVER['INFRASTRUCTURE_SCHEDULER_DRIVER'] = 'manual';
        $_SERVER['INFRASTRUCTURE_REQUIRES_SUPERVISOR'] = 'true';
        $_SERVER['INFRASTRUCTURE_REQUIRES_LONG_RUNNING_PROCESSES'] = 'true';
        $_SERVER['INFRASTRUCTURE_QUEUE_FALLBACK_CONNECTION'] = 'database';
        $_SERVER['INFRASTRUCTURE_REQUIRES_WEBSOCKETS'] = 'true';
        $_SERVER['INFRASTRUCTURE_REQUIRES_REDIS'] = 'true';
        $_SERVER['CACHE_STORE'] = 'file';
        $_SERVER['QUEUE_CONNECTION'] = 'sync';
        $_SERVER['SESSION_DRIVER'] = 'file';
        $_SERVER['MAIL_MAILER'] = 'array';
        $_SERVER['BROADCAST_CONNECTION'] = 'null';

        $config = require config_path('infrastructure.php');

        $this->assertFalse($config['shared_hosting']['compatible']);
        $this->assertSame('manual', $config['shared_hosting']['scheduler']['driver']);
        $this->assertTrue($config['shared_hosting']['workers']['requires_supervisor']);
        $this->assertTrue($config['shared_hosting']['workers']['requires_long_running_processes']);
        $this->assertSame('database', $config['shared_hosting']['workers']['queue_fallback_connection']);
        $this->assertTrue($config['shared_hosting']['realtime']['requires_websockets']);
        $this->assertTrue($config['shared_hosting']['services']['requires_redis']);
        $this->assertSame('file', $config['defaults']['cache_store']);
        $this->assertSame('sync', $config['defaults']['queue_connection']);
        $this->assertSame('file', $config['defaults']['session_driver']);
        $this->assertSame('array', $config['defaults']['mail_mailer']);
        $this->assertNull($config['defaults']['broadcast_connection']);

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
