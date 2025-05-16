<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Common\Traits\HaveAddresses;
use App\Domain\Common\Traits\HaveBankAccounts;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

/**
 * @property string                   $id
 * @property string                   $name
 * @property string                   $slug
 * @property ?string                  $taxId
 * @property ?string                  $email
 * @property ?string                  $phone
 * @property ?string                  $website
 * @property ?string                  $country
 * @property ?string                  $description
 * @property ?string                  $owner_id
 * @property Carbon                   $created_at
 * @property Carbon                   $updated_at
 * @property ?Carbon                  $deleted_at
 * @property ?User                    $owner
 * @property Collection|Address[]     $addresses
 * @property Collection|BankAccount[] $bankAccounts
 * @property Collection|Media[]       $media
 */
class Tenant extends BaseModel implements HasMedia
{
    use HasUuids;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasMediaSignedUrls;
    use HaveAddresses;
    use HaveBankAccounts;

    public static ?string $BYPASSED_TENANT_ID = null;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tenants')
            ->using(UserTenant::class)
            ->withPivot(['role'])
            ->withTimestamps()
        ;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;

        $this->addMediaCollection('attachments');
    }

    public function registerAllMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(config('domains.tenants.logo.size', 256))
            ->height(config('domains.tenants.logo.size', 256))
            ->nonQueued()
        ;
    }

    public function getMediaUrl(string $collectionName, string $fileName): string
    {
        if ('logo' === $collectionName) {
            return $this->getMediaSignedUrl($collectionName, $fileName);
        }

        return $this->getFirstMediaUrl($collectionName, $fileName);
    }

    public static function bypassTenant(string $tenantId, \Closure $callback): void
    {
        $previousTenantId         = self::$BYPASSED_TENANT_ID;
        self::$BYPASSED_TENANT_ID = $tenantId;
        $callback();
        self::$BYPASSED_TENANT_ID = $previousTenantId;
    }
}
