<?php

namespace App\Domain\Auth\Models;

use App\Domain\Auth\Casts\UserConfigCast;
use App\Domain\Auth\Enums\UserStatus;
use App\Domain\Auth\Notifications\ResetPasswordNotification;
use App\Domain\Auth\Notifications\VerifyEmailNotification;
use App\Domain\Auth\ValueObjects\UserConfig;
use App\Domain\Common\Model\BankAccount;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Common\Traits\HaveAddresses;
use App\Domain\Common\Traits\HaveBankAccounts;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectUser;
use App\Domain\Projects\Models\Task;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\UserTenant;
use App\Domain\Users\Models\NotificationSetting;
use App\Domain\Users\Models\SecurityEvent;
use App\Domain\Users\Models\TrustedDevice;
use App\Domain\Users\Models\UserPreference;
use App\Domain\Users\Models\UserProfile;
use App\Domain\Users\Models\UserTableSetting;
use App\Traits\UuidNotifiable;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\CropPosition;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string                        $id
 * @property string                        $first_name
 * @property string                        $last_name
 * @property string                        $email
 * @property string                        $password
 * @property ?string                       $description
 * @property ?string                       $birth_date
 * @property ?string                       $phone
 * @property bool                          $is_admin
 * @property UserStatus                    $status
 * @property UserConfig                    $config
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 * @property ?Carbon                       $deleted_at
 * @property ?Carbon                       $email_verified_at
 * @property ?UserSettings                 $settings
 * @property Collection<int, Address>      $addresses
 * @property Collection<int, BankAccount>  $bankAccounts
 * @property Collection<int, Media>        $media
 * @property Collection<int, OAuthAccount> $oauthAccounts
 * @property Collection<int, Project>      $projects
 * @property Collection<int, Skill>        $skills
 * @property Collection<int, Task>         $tasks
 * @property Collection<int, Tenant>       $tenants
 * @property Collection<int, UserSkill>    $userSkills
 */
class User extends Authenticatable implements JWTSubject, HasMedia, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use UuidNotifiable;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasMediaSignedUrls;
    use MustVerifyEmailTrait;
    use HaveBankAccounts;
    use HaveAddresses;
    use HasRoles;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'description',
        'birth_date',
        'is_admin',
        'phone',
        'status',
        'config',
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
        'config'            => UserConfigCast::class,
    ];

    protected static function booted(): void
    {
        static::created(function (User $user) {
            event(new \App\Domain\Auth\Events\UserCreated($user));
        });
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    protected function publicEmail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->config?->isEmailPublic ? $this->email : null,
        );
    }

    protected function publicBirthDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->config?->isBirthDatePublic ? $this->birth_date : null,
        );
    }

    protected function publicPhone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->config?->isPhonePublic ? $this->phone : null,
        );
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isActive(): bool
    {
        return UserStatus::ACTIVE === $this->status;
    }

    public function isEmailVerified(): bool
    {
        return null !== $this->email_verified_at;
    }

    public function isTwoFactorEnabled(): bool
    {
        return (bool) $this->settings?->two_factor_enabled ?? false;
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

        return null;
    }

    public function isCurrentTenant(Tenant $tenant): bool
    {
        return $this->getTenantId() === $tenant->id;
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function tableSettings(): HasMany
    {
        return $this->hasMany(UserTableSetting::class);
    }

    public function notificationSettings(): HasMany
    {
        return $this->hasMany(NotificationSetting::class);
    }

    public function trustedDevices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function securityEvents(): HasMany
    {
        return $this->hasMany(SecurityEvent::class);
    }

    public function oauthAccounts(): HasMany
    {
        return $this->hasMany(OAuthAccount::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_users')
            ->using(ProjectUser::class)
            ->withPivot(['role'])
            ->withTimestamps()
        ;
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function userSkills(): HasMany
    {
        return $this->hasMany(UserSkill::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->using(UserSkill::class)
            ->withPivot(['proficiency'])
            ->withTimestamps()
        ;
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'user_tenants')
            ->using(UserTenant::class)
            ->withPivot(['role'])
            ->withTimestamps()
        ;
    }

    public function organizationUnits()
    {
        return $this->belongsToMany(OrganizationUnit::class, 'org_unit_user')
            ->withPivot('role')
            ->withTimestamps()
        ;
    }

    public function emailVerificationToken(): HasOne
    {
        return $this->hasOne(EmailVerificationToken::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
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

    public function registerAllMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->crop(
                config('domains.users.avatar.size', 256),
                config('domains.users.avatar.size', 256),
                CropPosition::Center,
            )
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

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getMediaUrl(string $collectionName, string $fileName): string
    {
        if ('profile' === $collectionName) {
            return route('user.profile-image.showForUser', ['user' => $this->id], absolute: false);
        }

        return $this->getFirstMediaUrl($collectionName, $fileName);
    }
}
