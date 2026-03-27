<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'email',
    'token_hash',
    'invited_by',
    'accepted_user_id',
    'expires_at',
    'accepted_at',
])]
class UserInvitation extends Model
{
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function acceptedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_user_id');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && ! $this->hasExpired();
    }

    public function hasExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function matchesToken(string $token): bool
    {
        return hash_equals($this->token_hash, hash('sha256', $token));
    }
}
