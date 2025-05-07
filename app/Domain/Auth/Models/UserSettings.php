<?php

namespace App\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'language',
        'theme',
        'timezone',
        'two_factor_enabled',
        'two_factor_confirmed',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'preferences',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'two_factor_enabled'   => 'boolean',
        'two_factor_confirmed' => 'boolean',
        'preferences'          => 'array',
    ];

    /**
     * Get the user that owns the settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function defaults(): array
    {
        return [
            'language'                  => null,
            'theme'                     => null,
            'timezone'                  => null,
            'two_factor_enabled'        => false,
            'two_factor_confirmed'      => false,
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'preferences'               => null,
        ];
    }
}
