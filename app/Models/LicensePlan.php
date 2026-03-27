<?php

namespace App\Models;

use App\Domain\Licensing\LicenseDurationType;
use Database\Factories\LicensePlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'app_id',
    'name',
    'code',
    'duration_type',
    'duration_days',
    'default_max_devices',
    'is_active',
])]
class LicensePlan extends Model
{
    /** @use HasFactory<LicensePlanFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'duration_type' => LicenseDurationType::class,
            'duration_days' => 'integer',
            'default_max_devices' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'plan_id');
    }
}
