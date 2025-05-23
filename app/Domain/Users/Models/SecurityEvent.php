<?php

namespace App\Domain\Users\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $event_type
 * @property string $ip_address
 * @property User $user
 */
class SecurityEvent extends BaseModel
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
