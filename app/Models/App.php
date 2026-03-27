<?php

namespace App\Models;

use Database\Factories\AppFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'slug',
    'app_id',
    'is_active',
])]
class App extends Model
{
    /** @use HasFactory<AppFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function licensePlans(): HasMany
    {
        return $this->hasMany(LicensePlan::class);
    }
}
