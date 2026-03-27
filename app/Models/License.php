<?php

namespace App\Models;

use App\Domain\Licensing\LicenseStatus;
use Database\Factories\LicenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'app_id',
    'plan_id',
    'public_key',
    'license_key',
    'customer_name',
    'customer_email',
    'status',
    'max_devices',
    'expires_at',
    'grace_hours',
    'notes',
    'last_validated_at',
])]
class License extends Model
{
    /** @use HasFactory<LicenseFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => LicenseStatus::class,
            'max_devices' => 'integer',
            'grace_hours' => 'integer',
            'expires_at' => 'datetime',
            'last_validated_at' => 'datetime',
        ];
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(LicensePlan::class, 'plan_id');
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(LicenseEntitlement::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }
}
