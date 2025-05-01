<?php

namespace App\Domain\Auth\Models;

use App\Domain\Auth\Enums\UserStatus;
use App\Domain\Auth\Notifications\VerifyEmailNotification;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\UserTenant;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string                                               $id
 * @property string                                               $first_name
 * @property string                                               $last_name
 * @property string                                               $email
 * @property string                                               $password
 * @property ?string                                              $description
 * @property ?string                                              $birth_date
 * @property ?string                                              $phone
 * @property ?string                                              $avatar_url
 * @property bool                                                 $is_admin
 * @property UserStatus                                           $status
 * @property Carbon                                               $created_at
 * @property Carbon                                               $updated_at
 * @property ?Carbon                                              $deleted_at
 * @property ?Carbon                                              $email_verified_at
 * @property UserSettings|null                                    $settings
 * @property Collection<int, OAuthAccount>                        $oauthAccounts
 * @property Collection<int, UserTenant>                          $tenantMemberships
 * @property Collection<int, \App\Domain\Skills\Models\UserSkill> $skills
 * @property Collection<int, Tenant>                              $tenants
 */
class User extends Authenticatable implements JWTSubject, HasMedia, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;
    use InteractsWithMedia;
    use MustVerifyEmailTrait;

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
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'birth_date'        => 'date',
        'is_admin'          => 'boolean',
        'status'            => UserStatus::class,
    ];

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isActive(): bool
    {
        return UserStatus::ACTIVE === $this->status;
    }

    // TODO: Implement this
    public function isTwoFactorEnabled(): string
    {
        return $this->settings?->two_factor_enabled ?? false;
    }

    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        $claims = [
            'ev' => $this->hasVerifiedEmail() ? 1 : 0,
        ];

        if ($this->isTwoFactorEnabled()) {
            $claims['mfa'] = 0; // Default to not passed
        }

        return $claims;
    }

    public function getTenantId(): ?string
    {
        if (Auth::check() && Auth::payload()?->get('tid')) {
            return Auth::payload()->get('tid');
        }

        $membership = $this->tenantMemberships()->first();

        if ($membership) {
            return $membership->tenant_id;
        }

        return null;
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function oauthAccounts(): HasMany
    {
        return $this->hasMany(OAuthAccount::class);
    }

    public function tenantMemberships(): HasMany
    {
        return $this->hasMany(UserTenant::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(\App\Domain\Skills\Models\UserSkill::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'user_tenants')
            ->using(UserTenant::class)
            ->withPivot(['role'])
            ->withTimestamps()
        ;
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    // === MEDIA LIBRARY CONFIG ===

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 100, 100)
            ->nonQueued()
        ;
    }

    // Optional accessor
    public function getAvatarThumbUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('profile', 'thumb');
    }

    public function getAvatarOriginalUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('profile');
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
}
