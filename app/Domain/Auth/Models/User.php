<?php

namespace App\Domain\Auth\Models;

use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use App\Domain\Tenant\Models\UserTenant;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Session;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property ?string $description
 * @property ?string $birth_date
 * @property ?string $phone
 * @property ?string $avatar_url
 * @property bool $is_admin
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
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
     * @var list<string>
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
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
     * Get the user's current tenant ID from session or active membership.
     * TODO: Handle JWT token.
     *
     * @return string|null
     */
    public function getTenantId(): ?string
    {
        // First try to get from session
        $tenantId = Session::get('current_tenant_id');

        if ($tenantId) {
            return $tenantId;
        }

        // If not in session, get from first active membership
        $membership = $this->tenantMemberships()->first();

        if ($membership) {
            // Store in session for future use
            Session::put('current_tenant_id', $membership->tenant_id);
            return $membership->tenant_id;
        }

        return null;
    }

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
        return $this->hasMany(UserTenant::class);
    }

    /**
     * Get the user's skills.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(\App\Domain\Skills\Models\UserSkill::class);
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
