<?php

namespace App\Support\Licensing;

final readonly class ActivateLicenseResult
{
    /**
     * @param  array<int, array{featureCode:string, enabled:bool}>  $entitlements
     */
    public function __construct(
        public ?string $activationId,
        public ?string $activationToken,
        public string $licenseStatus,
        public ?string $graceUntil,
        public array $entitlements,
        public ?string $reasonCode,
    ) {
    }
}
