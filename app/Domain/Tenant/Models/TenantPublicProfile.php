<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * @property ?string $public_name
 * @property ?string $description
 * @property ?string $website_url
 * @property ?array  $social_links
 * @property bool    $visible
 * @property ?string $industry
 * @property ?string $location_city
 * @property ?string $location_country
 * @property ?string $address
 * @property ?Media  $public_logo
 * @property ?Media  $banner_image
 */
class TenantPublicProfile extends BaseModel implements HasMedia
{
    use BelongsToTenant;
    use InteractsWithMedia;
    use HasMediaSignedUrls;

    protected $fillable = [
        'tenant_id',
        'public_name',
        'description',
        'website_url',
        'social_links',
        'visible',
        'industry',
        'location_city',
        'location_country',
        'address',
    ];

    protected $casts = [
        'social_links' => 'array',
        'visible'      => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('public_logo')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;

        $this->addMediaCollection('banner_image')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;
    }

    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(config('domains.tenants.logo.size', 256))
            ->height(config('domains.tenants.logo.size', 256))
        ;

        $this->addMediaConversion('banner')
            ->width(config('domains.tenants.banner_image.size', 1200))
            ->height(config('domains.tenants.banner_image.size', 400))
        ;
    }
}
