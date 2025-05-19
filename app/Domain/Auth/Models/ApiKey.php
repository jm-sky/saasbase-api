<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'key',
        'scopes',
        'last_used_at',
    ];

    protected $casts = [
        'scopes'       => 'array',
        'last_used_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
