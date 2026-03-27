<?php

namespace App\Services\Licensing;

use App\Models\License;
use Illuminate\Support\Str;

class LicenseIdentifierGenerator
{
    private const LICENSE_KEY_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function generateLicenseKey(): string
    {
        return $this->generateUnique(fn (): string => $this->randomLicenseKey(), 'license_key');
    }

    public function generatePublicKey(): string
    {
        return $this->generateUnique(
            fn (): string => config('licensing.identifiers.public_key_prefix', 'lic_').Str::lower(Str::random(32)),
            'public_key',
        );
    }

    private function randomLicenseKey(): string
    {
        $groups = [];

        for ($group = 0; $group < 4; $group++) {
            $segment = '';

            for ($char = 0; $char < 4; $char++) {
                $segment .= self::LICENSE_KEY_ALPHABET[random_int(0, strlen(self::LICENSE_KEY_ALPHABET) - 1)];
            }

            $groups[] = $segment;
        }

        return config('licensing.identifiers.license_key_prefix', 'XARGO').'-'.implode('-', $groups);
    }

    private function generateUnique(callable $generator, string $column): string
    {
        do {
            $value = $generator();
        } while (License::query()->where($column, $value)->exists());

        return $value;
    }
}
