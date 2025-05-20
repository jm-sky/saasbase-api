<?php

namespace App\Domain\Users\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'avatar_url',
        'bio',
        'location',
        'birth_date',
        'position',
        'website',
        'social_links',
    ];

    protected $casts = [
        'birth_date'   => 'date',
        'social_links' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
