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

class TenantBranding extends BaseModel implements HasMedia
{
    use HasUuids;
    use BelongsToTenant;
    use InteractsWithMedia;
    use HasMediaSignedUrls;

    protected $fillable = [
        'tenant_id',
        'color_primary',
        'color_secondary',
        'short_name',
        'theme',
        'pdf_accent_color',
        'email_signature_html',
    ];

    protected $casts = [
        'theme' => 'string',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;

        $this->addMediaCollection('favicon')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/x-icon', 'image/png']))
        ;

        $this->addMediaCollection('custom_font')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['font/woff', 'font/woff2']))
        ;

        $this->addMediaCollection('pdf_logo')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;

        $this->addMediaCollection('email_header_image')
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

        $this->addMediaConversion('pdf')
            ->width(800)
            ->height(800)
            ->nonQueued()
        ;

        $this->addMediaConversion('email')
            ->width(600)
            ->height(200)
            ->nonQueued()
        ;
    }
}
