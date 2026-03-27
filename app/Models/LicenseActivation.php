<?php

namespace App\Models;

use App\Domain\Licensing\LicenseActivationStatus;
use Database\Factories\LicenseActivationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'license_id',
    'activation_id',
    'machine_id',
    'installation_id',
    'activation_token_hash',
    'device_label',
    'status',
    'first_seen_at',
    'last_seen_at',
    'grace_until',
    'last_reason_code',
])]
class LicenseActivation extends Model
{
    /** @use HasFactory<LicenseActivationFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => LicenseActivationStatus::class,
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'grace_until' => 'datetime',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(LicenseHeartbeat::class, 'license_activation_id');
    }

    public function matchesActivationToken(string $token): bool
    {
        return hash_equals($this->activation_token_hash, hash('sha256', $token));
    }

    public function matchesDevice(string $machineId, string $installationId): bool
    {
        return $this->machine_id === $machineId
            && $this->installation_id === $installationId;
    }
}
