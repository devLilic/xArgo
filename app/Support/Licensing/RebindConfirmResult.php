<?php

namespace App\Support\Licensing;

final readonly class RebindConfirmResult
{
    public function __construct(
        public bool $confirmed,
        public ?string $activationId,
        public string $licenseStatus,
        public ?string $graceUntil,
        public ?string $reasonCode,
    ) {
    }
}
