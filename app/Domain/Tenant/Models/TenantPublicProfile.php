<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

class TenantPublicProfile extends BaseModel implements HasMedia
{
    use HasUuids;
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

    public function registerMediaConversions(): void
    {
        $this->addMediaConversion('thumb')
            ->width(256)
            ->height(256)
            ->nonQueued()
        ;

        $this->addMediaConversion('banner')
            ->width(1200)
            ->height(400)
            ->nonQueued()
        ;
    }
}
