<?php

namespace App\Domain\Users\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'language',
        'decimal_separator',
        'date_format',
        'dark_mode',
        'is_sound_enabled',
        'is_profile_public',
        'field_visibility',
        'visibility_per_tenant',
    ];

    protected $casts = [
        'is_sound_enabled'      => 'boolean',
        'is_profile_public'     => 'boolean',
        'field_visibility'      => 'array',
        'visibility_per_tenant' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
