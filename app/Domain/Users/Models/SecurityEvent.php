<?php

namespace App\Domain\Users\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
