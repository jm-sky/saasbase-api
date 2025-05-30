<?php

namespace App\Domain\Users\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string  $id
 * @property string  $user_id
 * @property ?string $bio
 * @property ?string $location
 * @property ?string $birth_date
 * @property ?string $position
 * @property ?string $website
 * @property array   $social_links
 * @property User    $user
 */
class UserProfile extends Model
{
    use HasUuids;

    protected $with = ['user'];

    protected $fillable = [
        'user_id',
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
