<?php

namespace App\Domain\Users\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustedDevice extends BaseModel
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'token',
        'device_name',
        'browser',
        'os',
        'location',
        'last_active_at',
        'ip_address',
        'trusted_until',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'trusted_until'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
