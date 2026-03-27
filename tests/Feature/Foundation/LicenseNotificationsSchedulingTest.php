<?php

namespace Tests\Feature\Foundation;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LicenseNotificationsSchedulingTest extends TestCase
{
    public function test_license_notification_command_is_registered_with_the_scheduler(): void
    {
        $exitCode = Artisan::call('schedule:list');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('0 3 * * *', $output);
        $this->assertStringContainsString('licensing:send-notifications', $output);
    }
}
