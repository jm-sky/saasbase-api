<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Concerns\HasMediaUrl;
use App\Domain\Common\Models\Address;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Models\Tag;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Common\Traits\HaveAddresses;
use App\Domain\Common\Traits\HaveBankAccounts;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Expense\Models\Expense;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Products\Models\Product;
use App\Domain\Projects\Models\Project;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\BillingInfo;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Enums\TenantActivityType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * @property string                        $id
 * @property string                        $name
 * @property string                        $slug
 * @property ?string                       $vat_id
 * @property ?string                       $tax_id
 * @property ?string                       $regon
 * @property ?string                       $country
 * @property ?string                       $email
 * @property ?string                       $phone
 * @property ?string                       $website
 * @property ?string                       $description
 * @property ?string                       $owner_id
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 * @property ?Carbon                       $deleted_at
 * @property ?User                         $owner
 * @property ?TenantPreferences            $preferences
 * @property ?TenantBranding               $branding
 * @property ?TenantPublicProfile          $publicProfile
 * @property Collection<Tag>               $tags
 * @property Collection<Address>           $addresses
 * @property Collection<BankAccount>       $bankAccounts
 * @property Collection<Media>             $media
 * @property Collection<TenantInvitation>  $invitations
 * @property Collection<TenantIntegration> $integrations
 * @property Collection<Project>           $projects
 * @property Collection<Contractor>        $contractors
 * @property Collection<Product>           $products
 * @property Collection<OrganizationUnit>  $organizationUnits
 * @property Collection<Invoice>           $invoices
 * @property Collection<Expense>           $expenses
 * @property ?BillingCustomer              $billingCustomer
 * @property ?BillingInfo                  $billingInfo
 * @property ?Subscription                 $subscription
 * @property ?Subscription                 $currentSubscription
 * @property ?OrganizationUnit             $rootOrganizationUnit
 * @property ?OrganizationUnit             $unassignedOrganizationUnit
 * @property ?OrganizationUnit             $formerEmployeesOrganizationUnit
 */
class Tenant extends BaseModel implements HasMedia, HasMediaUrl
{
    use SoftDeletes;
    use InteractsWithMedia;
    use HasMediaSignedUrls;
    use HaveAddresses;
    use HaveBankAccounts;
    use HasActivityLog;
    use HasActivityLogging;

    public const GLOBAL_TENANT_ID = null;

    public const NONE_TENANT_ID = 'none';

    public static ?string $BYPASSED_TENANT_ID = self::NONE_TENANT_ID;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'email',
        'phone',
        'notes',
        'is_active',
        'vat_id',
        'tax_id',
        'regon',
        'website',
        'country',
        'description',
    ];

    protected $casts = [
        'id'        => 'string',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($tenant) {
            $tenant->logModelActivity(TenantActivityType::Created->value, $tenant);
        });

        static::updated(function ($tenant) {
            $tenant->logModelActivity(TenantActivityType::Updated->value, $tenant);
        });

        static::deleted(function ($tenant) {
            $tenant->logModelActivity(TenantActivityType::Deleted->value, $tenant);
        });
    }

    public function getTenantId(): string
    {
        return $this->id;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tenants')
            ->using(UserTenant::class)
            ->withPivot(['role'])
            ->withTimestamps()
        ;
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(TenantPreferences::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class);
    }

    public function branding(): HasOne
    {
        return $this->hasOne(TenantBranding::class);
    }

    public function publicProfile(): HasOne
    {
        return $this->hasOne(TenantPublicProfile::class);
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

    public function currentSubscription(): ?Subscription
    {
        // @phpstan-ignore-next-line
        return $this->subscription()->where(function ($query) {
            $query->where('status', 'active')->orWhere('status', 'trialing');
        })->first();
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(TenantIntegration::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function contractors(): HasMany
    {
        return $this->hasMany(Contractor::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function organizationUnits(): HasMany
    {
        return $this->hasMany(OrganizationUnit::class);
    }

    public function rootOrganizationUnit(): HasOne
    {
        return $this->hasOne(OrganizationUnit::class)->whereNull('parent_id');
    }

    public function unassignedOrganizationUnit(): HasOne
    {
        return $this->hasOne(OrganizationUnit::class)->unassigned();
    }

    public function formerEmployeesOrganizationUnit(): HasOne
    {
        return $this->hasOne(OrganizationUnit::class)->formerEmployees();
    }

    public function attachUserToRootOrganizationUnit(User $user, OrgUnitRole $role): void
    {
        OrgUnitUser::firstOrCreate([
            'organization_unit_id' => $this->rootOrganizationUnit->id,
            'user_id'              => $user->id,
        ], [
            'role' => $role->value,
        ]);
    }

    public function attachUserToUnassignedOrganizationUnit(User $user): void
    {
        OrgUnitUser::firstOrCreate([
            'organization_unit_id' => $this->unassignedOrganizationUnit->id,
            'user_id'              => $user->id,
        ], [
            'role' => OrgUnitRole::Employee->value,
        ]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;

        $this->addMediaCollection('attachments');
    }

    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(config('domains.tenants.logo.size', 256))
            ->height(config('domains.tenants.logo.size', 256))
        ;
    }

    public function getMediaUrl(string $collectionName, string $fileName): string
    {
        if ('logo' === $collectionName) {
            return $this->getMediaSignedUrl($collectionName, $fileName);
        }

        return $this->getFirstMediaUrl($collectionName, $fileName);
    }

    public static function bypassTenant(?string $tenantId, \Closure $callback): mixed
    {
        $previousTenantId         = self::$BYPASSED_TENANT_ID;
        self::$BYPASSED_TENANT_ID = $tenantId;

        $result = $callback();

        self::$BYPASSED_TENANT_ID = $previousTenantId;

        return $result;
    }
}
