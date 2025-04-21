<?php

namespace App\Domain\Auth\Models;

use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\UserTenant;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection as Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string              $id
 * @property string              $first_name
 * @property string              $last_name
 * @property string              $email
 * @property string              $password
 * @property ?string             $description
 * @property ?string             $birth_date
 * @property ?string             $phone
 * @property ?string             $avatar_url
 * @property bool                $is_admin
 * @property Carbon              $created_at
 * @property Carbon              $updated_at
 * @property ?Carbon             $deleted_at
 *
 * @property-read UserSettings|null                      $settings
 * @property-read Collection<int, OAuthAccount>          $oauthAccounts
 * @property-read Collection<int, UserTenant>            $tenantMemberships
 * @property-read Collection<int, \App\Domain\Skills\Models\UserSkill> $skills
 * @property-read Collection<int, Tenant>                $tenants
 */
class User extends Authenticatable implements JWTSubject
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
        'password'          => 'hashed',
        'birth_date'        => 'date',
        'is_admin'          => 'boolean',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'tenant_id' => $this->getTenantId() ?? $this->tenants()->first()?->id,
            'email'     => $this->email,
            'role'      => $this->role,
        ];
    }

    /**
     * Get the user's current tenant ID from session or active membership.
     * TODO: Handle JWT token.
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

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
            ->withPivot(['role'])
            ->withTimestamps()
        ;
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
