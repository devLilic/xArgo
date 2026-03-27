<?php

namespace Tests\Feature\Apps;

use App\Actions\Licensing\TransitionLicenseStatusAction;
use App\Domain\Licensing\LicenseStatus;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransitionLicenseStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_transitions_a_license_between_supported_statuses(): void
    {
        $license = License::factory()->create([
            'status' => LicenseStatus::ACTIVE,
        ]);

        $action = app(TransitionLicenseStatusAction::class);

        $this->assertSame(LicenseStatus::SUSPENDED, $action->execute($license, 'suspend')->status);
        $this->assertSame(LicenseStatus::REVOKED, $action->execute($license->fresh(), 'revoke')->status);
        $this->assertSame(LicenseStatus::ACTIVE, $action->execute($license->fresh(), 'reactivate')->status);
    }
}
