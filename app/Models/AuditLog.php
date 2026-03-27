<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'action',
    'entity_type',
    'entity_id',
    'meta_json',
])]
class AuditLog extends Model
{
    protected $table = 'admin_audit_logs';

    protected function casts(): array
    {
        return [
            'meta_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
