<?php

namespace App\Domain\Auth\Models;

use App\Domain\Auth\Notifications\ResetPasswordNotification;
use App\Domain\Auth\Notifications\VerifyEmailNotification;
use App\Domain\Auth\Traits\HasUsersPublicScopedFields;
use App\Domain\Auth\Traits\HasUsersTenantScopedFields;
use App\Domain\Common\Concerns\HasMediaUrl;
use App\Domain\Common\Models\Address;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Common\Traits\HaveAddresses;
use App\Domain\Common\Traits\HaveBankAccounts;
use App\Domain\Common\Traits\IsSearchable;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectUser;
use App\Domain\Projects\Models\Task;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\BillingInfo;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
use App\Domain\Tenant\Models\Position;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\UserTenant;
use App\Domain\Users\Models\NotificationSetting;
use App\Domain\Users\Models\SecurityEvent;
use App\Domain\Users\Models\TrustedDevice;
use App\Domain\Users\Models\UserPreference;
use App\Domain\Users\Models\UserProfile;
use App\Domain\Users\Models\UserTableSetting;
use App\Traits\UlidNotifiable;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\CropPosition;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string                               $id
 * @property string                               $first_name
 * @property string                               $last_name
 * @property string                               $email
 * @property string                               $password
 * @property ?string                              $phone
 * @property bool                                 $is_admin
 * @property bool                                 $is_active
 * @property Carbon                               $created_at
 * @property Carbon                               $updated_at
 * @property ?Carbon                              $deleted_at
 * @property ?Carbon                              $email_verified_at
 * @property ?string                              $tenant_id
 * @property ?UserSettings                        $settings
 * @property ?UserPreference                      $preferences
 * @property ?UserProfile                         $profile
 * @property ?UserPersonalData                    $personalData
 * @property ?string                              $name                     User's full name (first_name + last_name)
 * @property ?string                              $full_name                User's full name (first_name + last_name)
 * @property ?string                              $public_email
 * @property ?string                              $public_birth_date
 * @property ?string                              $public_phone
 * @property ?string                              $tenant_scoped_email
 * @property ?string                              $tenant_scoped_birth_date
 * @property ?string                              $tenant_scoped_phone
 * @property ?BillingCustomer                     $billingCustomer
 * @property ?BillingInfo                         $billingInfo
 * @property ?Subscription                        $subscription
 * @property Collection<int, Address>             $addresses
 * @property Collection<int, BankAccount>         $bankAccounts
 * @property Collection<int, Media>               $media
 * @property Collection<int, OAuthAccount>        $oauthAccounts
 * @property Collection<int, Project>             $projects
 * @property Collection<int, Skill>               $skills
 * @property Collection<int, Task>                $tasks
 * @property Collection<int, Tenant>              $tenants
 * @property Collection<int, UserSkill>           $userSkills
 * @property Collection<int, UserSession>         $sessions
 * @property Collection<int, UserTableSetting>    $tableSettings
 * @property Collection<int, NotificationSetting> $notificationSettings
 * @property Collection<int, TrustedDevice>       $trustedDevices
 * @property Collection<int, SecurityEvent>       $securityEvents
 * @property Collection<int, ApiKey>              $apiKeys
 */
class User extends Authenticatable implements JWTSubject, HasMedia, HasMediaUrl, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasUlids;
    // use UlidNotifiable;
    use Notifiable;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasMediaSignedUrls;
    use MustVerifyEmailTrait;
    use HaveBankAccounts;
    use HaveAddresses;
    use HasRoles;
    use IsSearchable;
    use HasUsersTenantScopedFields;
    use HasUsersPublicScopedFields;

    protected $with = ['preferences'];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'is_admin',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_admin'          => 'boolean',
        'is_active'         => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (User $user) {
            event(new \App\Domain\Auth\Events\UserCreated($user));
        });
    }

    protected function tenantId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->getTenantId(),
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isActive(): bool
    {
        return $this->is_active;
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

    public function personalData(): HasOne
    {
        return $this->hasOne(UserPersonalData::class);
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

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_users')
            ->using(ProjectUser::class)
            ->withPivot(['project_role_id'])
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
        return $this->belongsToMany(Skill::class, 'user_skill')
            ->using(UserSkill::class)
            ->withPivot(['level', 'acquired_at', 'id'])
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

    public function currentTenant(): ?Tenant
    {
        $tenantId = $this->getTenantId();

        if (!$tenantId) {
            return null;
        }

        // @phpstan-ignore-next-line
        return $this->tenants()->find($tenantId);
    }

    public function organizationUnits()
    {
        return $this->belongsToMany(OrganizationUnit::class, 'org_unit_user')
            ->withPivot('role')
            ->withTimestamps()
        ;
    }

    public function orgUnitUsers(): HasMany
    {
        return $this->hasMany(OrgUnitUser::class, 'user_id');
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

    public function billingCustomer(): HasOne
    {
        return $this->hasOne(BillingCustomer::class, 'billable_id', 'id');
    }

    public function billingInfo(): HasOne
    {
        return $this->hasOne(BillingInfo::class, 'billable_id', 'id');
    }

    public function subscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'billable');
    }

    // === MEDIA LIBRARY CONFIG ===

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;

        $this->addMediaCollection('identity_confirmation_template')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['application/xml', 'text/xml']))
        ;
    }

    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->crop(
                config('domains.users.avatar.size', 256),
                config('domains.users.avatar.size', 256),
                CropPosition::Center,
            )
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

    public function positions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Position::class,
            OrgUnitUser::class,
            'user_id',
            'id',
            'id',
            'position_id'
        );
    }

    public function currentPositions(): HasManyThrough
    {
        return $this->positions()
            ->whereHas('orgUnitUsers', function ($query) {
                /* @phpstan-ignore-next-line */
                $query->where('user_id', $this->id)->active();
            })
        ;
    }

    public function primaryPosition(): ?Position
    {
        /** @var ?OrgUnitUser $primaryOrgUnit */
        $primaryOrgUnit = $this->orgUnitUsers()->activePrimary()->first(); // @phpstan-ignore-line

        return $primaryOrgUnit?->position;
    }

    // Assign user to organization unit with position
    public function assignToPosition(OrganizationUnit $unit, ?Position $position = null, array $options = []): OrgUnitUser
    {
        $options = array_merge([
            'valid_from' => now(),
            'is_primary' => false,
            'notes'      => null,
            'role'       => \App\Domain\Tenant\Enums\OrgUnitRole::Employee,
        ], $options);

        // Create org unit user assignment
        /** @var OrgUnitUser $orgUnitUser */
        $orgUnitUser = $this->orgUnitUsers()->create([
            'tenant_id'            => $unit->tenant_id,
            'organization_unit_id' => $unit->id,
            'position_id'          => $position?->id,
            'role'                 => $options['role']->value,
            'is_primary'           => $options['is_primary'],
            'notes'                => $options['notes'],
            'valid_from'           => $options['valid_from'],
        ]);

        // Assign role if position has one
        if ($position && $position->role_name) {
            $this->assignRole($position->role_name);
        }

        return $orgUnitUser;
    }

    // Check user status
    public function isDirector(): bool
    {
        return $this->currentPositions()->where('is_director', true)->exists();
    }

    public function isLearning(): bool
    {
        return $this->currentPositions()->where('is_learning', true)->exists();
    }

    // Get user's position in specific unit
    public function getPositionInUnit(OrganizationUnit $unit): ?Position
    {
        /** @var ?OrgUnitUser $orgUnitUser */
        $orgUnitUser = $this->orgUnitUsers()->where('organization_unit_id', $unit->id)->active()->first(); // @phpstan-ignore-line

        return $orgUnitUser?->position;
    }
}
