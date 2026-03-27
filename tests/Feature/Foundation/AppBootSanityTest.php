<?php

namespace Tests\Feature\Foundation;

use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppBootSanityTest extends TestCase
{
    #[Test]
    public function the_application_container_boots_with_foundation_configs_loaded(): void
    {
        $this->assertInstanceOf(Application::class, $this->app);
        $this->assertTrue($this->app->isBooted());

        $this->assertIsArray(config('licensing'));
        $this->assertIsArray(config('infrastructure'));
    }
}
