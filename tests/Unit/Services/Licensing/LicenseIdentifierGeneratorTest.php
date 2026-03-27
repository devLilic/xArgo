<?php

namespace Tests\Unit\Services\Licensing;

use App\Services\Licensing\LicenseIdentifierGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseIdentifierGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_human_readable_license_keys(): void
    {
        $generator = app(LicenseIdentifierGenerator::class);

        $licenseKey = $generator->generateLicenseKey();

        $this->assertMatchesRegularExpression('/^XARGO-[A-Z2-9]{4}(?:-[A-Z2-9]{4}){3}$/', $licenseKey);
    }

    public function test_it_generates_non_guessable_public_keys(): void
    {
        $generator = app(LicenseIdentifierGenerator::class);

        $publicKey = $generator->generatePublicKey();

        $this->assertMatchesRegularExpression('/^lic_[a-zA-Z0-9]{32}$/', $publicKey);
    }
}
