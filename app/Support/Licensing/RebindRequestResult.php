<?php

namespace App\Support\Licensing;

final readonly class RebindRequestResult
{
    public function __construct(
        public bool $requested,
        public bool $requiresManualReview,
        public ?string $activationId,
        public string $licenseStatus,
        public ?string $graceUntil,
        public ?string $reasonCode,
    ) {
    }
}
