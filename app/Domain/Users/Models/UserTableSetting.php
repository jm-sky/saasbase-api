<?php

namespace App\Domain\Users\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTableSetting extends BaseModel
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'entity',
        'name',
        'config',
        'is_default',
    ];

    protected $casts = [
        'config'     => 'array',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
