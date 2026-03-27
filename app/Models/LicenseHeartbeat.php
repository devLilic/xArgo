<?php

namespace App\Models;

use Database\Factories\LicenseHeartbeatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'license_activation_id',
    'app_version',
    'received_at',
    'ip_address',
    'reason_code',
])]
class LicenseHeartbeat extends Model
{
    /** @use HasFactory<LicenseHeartbeatFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function activation(): BelongsTo
    {
        return $this->belongsTo(LicenseActivation::class, 'license_activation_id');
    }
}
