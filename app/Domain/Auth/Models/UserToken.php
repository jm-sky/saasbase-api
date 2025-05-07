<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;

class UserToken extends BaseModel
{
    protected $fillable = [
        'user_id',
        'token_id',
        'user_agent',
        'ip_address',
        'device_name',
        'last_used_at',
        'expires_at',
        'revoked',
    ];

    protected $casts = [
        'revoked'      => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
