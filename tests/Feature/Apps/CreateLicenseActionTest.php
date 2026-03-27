<?php

namespace Tests\Feature\Apps;

use App\Actions\Licensing\CreateLicenseAction;
use App\Domain\Licensing\LicenseStatus;
use App\Models\LicensePlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateLicenseActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_license_with_generated_identifiers(): void
    {
        $plan = LicensePlan::factory()->subscription(30)->create([
            'default_max_devices' => 3,
        ]);

        $license = app(CreateLicenseAction::class)->execute($plan, [
            'customer_name' => 'Ada Lovelace',
            'customer_email' => 'ada@example.com',
            'status' => LicenseStatus::ACTIVE,
            'notes' => 'Internal issue test license',
        ]);

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'app_id' => $plan->app_id,
            'plan_id' => $plan->id,
            'customer_name' => 'Ada Lovelace',
            'customer_email' => 'ada@example.com',
            'status' => LicenseStatus::ACTIVE->value,
            'max_devices' => 3,
            'notes' => 'Internal issue test license',
        ]);

        $this->assertMatchesRegularExpression('/^XARGO-[A-Z2-9]{4}(?:-[A-Z2-9]{4}){3}$/', $license->license_key);
        $this->assertMatchesRegularExpression('/^lic_[a-zA-Z0-9]{32}$/', $license->public_key);
    }

    public function test_it_generates_unique_identifiers_for_each_license(): void
    {
        $plan = LicensePlan::factory()->create();
        $action = app(CreateLicenseAction::class);

        $firstLicense = $action->execute($plan);
        $secondLicense = $action->execute($plan);

        $this->assertNotSame($firstLicense->license_key, $secondLicense->license_key);
        $this->assertNotSame($firstLicense->public_key, $secondLicense->public_key);
    }
}
