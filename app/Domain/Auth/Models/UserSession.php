<?php

namespace App\Domain\Auth\Models;

use App\Domain\Auth\Enums\SessionType;
use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string      $id
 * @property string      $user_id
 * @property SessionType $type
 * @property ?string     $token_id
 * @property ?string     $ip_address
 * @property ?string     $user_agent
 * @property ?string     $device_name
 * @property Carbon      $last_active_at
 * @property ?Carbon     $expires_at
 * @property ?Carbon     $revoked_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property User        $user
 */
class UserSession extends BaseModel
{
    protected $fillable = [
        'user_id',
        'type',
        'token_id',
        'ip_address',
        'user_agent',
        'device_name',
        'last_active_at',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'type'           => SessionType::class,
        'last_active_at' => 'datetime',
        'expires_at'     => 'datetime',
        'revoked_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCurrent(): bool
    {
        return $this->token_id === request()->bearerToken();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return null !== $this->revoked_at;
    }

    public function isActive(): bool
    {
        return !$this->isExpired() && !$this->isRevoked();
    }
}
