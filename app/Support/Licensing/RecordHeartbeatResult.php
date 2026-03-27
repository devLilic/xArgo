<?php

namespace App\Support\Licensing;

final readonly class RecordHeartbeatResult
{
    public function __construct(
        public bool $accepted,
        public ?string $activationId,
        public string $licenseStatus,
        public string $activationStatus,
        public ?string $graceUntil,
        public ?string $reasonCode,
    ) {
    }
}
