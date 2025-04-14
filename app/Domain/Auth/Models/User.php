<?php

namespace App\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property ?string $description
 * @property ?string $birth_date
 * @property ?string $phone
 * @property bool $is_admin
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property ?\Carbon\Carbon $deleted_at
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'description',
        'birth_date',
        'is_admin',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'is_admin' => 'boolean',
    ];

    /**
     * Get the user's settings.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    /**
     * Get the user's OAuth accounts.
     */
    public function oauthAccounts(): HasMany
    {
        return $this->hasMany(OAuthAccount::class);
    }

    /**
     * Get the user's tenant memberships.
     */
    public function tenantMemberships(): HasMany
    {
        return $this->hasMany(\App\Domain\Tenancy\Models\UserTenant::class);
    }

    /**
     * Get the user's skills.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(\App\Domain\Skills\Models\UserSkill::class);
    }
}
