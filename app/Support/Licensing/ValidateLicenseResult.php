<?php

namespace App\Support\Licensing;

final readonly class ValidateLicenseResult
{
    /**
     * @param  array<int, array{featureCode:string, enabled:bool}>  $entitlements
     */
    public function __construct(
        public bool $isValid,
        public ?string $activationId,
        public string $licenseStatus,
        public ?string $graceUntil,
        public array $entitlements,
        public ?string $reasonCode,
    ) {
    }
}
