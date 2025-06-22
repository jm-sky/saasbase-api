<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $color_primary
 * @property string $color_secondary
 * @property string $short_name
 * @property string $theme
 * @property string $pdf_accent_color
 * @property string $email_signature_html
 */
class TenantBranding extends BaseModel implements HasMedia
{
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

    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(config('domains.tenants.logo.size', 256))
            ->height(config('domains.tenants.logo.size', 256))
        ;

        $this->addMediaConversion('pdf')
            ->width(config('domains.tenants.pdf_logo.size', 800))
            ->height(config('domains.tenants.pdf_logo.size', 800))
        ;

        $this->addMediaConversion('email')
            ->width(config('domains.tenants.email_header_image.size', 600))
            ->height(config('domains.tenants.email_header_image.size', 200))
        ;
    }
}
