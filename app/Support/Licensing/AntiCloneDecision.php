<?php

namespace App\Support\Licensing;

use Carbon\CarbonInterface;

final readonly class AntiCloneDecision
{
    public function __construct(
        public bool $allowed,
        public bool $matchesBoundDevice,
        public bool $blocked,
        public ?string $reasonCode,
        public ?CarbonInterface $graceUntil,
    ) {
    }
}
