<?php

namespace App\Support\Licensing;

final readonly class IssuedActivationToken
{
    public function __construct(
        public string $plainTextToken,
        public string $hashedToken,
    ) {
    }
}
