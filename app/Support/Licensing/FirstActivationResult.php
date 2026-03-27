<?php

namespace App\Support\Licensing;

use App\Models\LicenseActivation;

final readonly class FirstActivationResult
{
    public function __construct(
        public LicenseActivation $activation,
        public string $plainTextToken,
    ) {
    }
}
