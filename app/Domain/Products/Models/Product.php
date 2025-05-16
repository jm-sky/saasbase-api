<?php

namespace App\Domain\Products\Models;

use App\Domain\Common\Models\Attachment;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\Comment;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Models\VatRate;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Common\Traits\HasTags;
use App\Domain\Common\Traits\HaveComments;
use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

/**
 * @property string                                  $id
 * @property string                                  $tenant_id
 * @property string                                  $name
 * @property ?string                                 $description
 * @property string                                  $unit_id
 * @property float                                   $price_net
 * @property ?string                                 $vat_rate_id
 * @property Carbon                                  $created_at
 * @property Carbon                                  $updated_at
 * @property ?Carbon                                 $deleted_at
 * @property MeasurementUnit                         $unit
 * @property ?VatRate                                $vatRate
 * @property \Illuminate\Support\Collection|string[] $tags
 */
class Product extends BaseModel implements HasMedia
{
    use SoftDeletes;
    use BelongsToTenant;
    use InteractsWithMedia;
    use HasMediaSignedUrls;
    use HasTags;
    use HaveComments;
    use HasActivityLog;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'unit_id',
        'price_net',
        'vat_rate_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_net' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    protected static function newFactory()
    {
        return ProductFactory::new();
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
            ->width(config('domains.products.logo.size', 256))
            ->height(config('domains.products.logo.size', 256))
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

    public function logo(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    protected static function booted()
    {
        static::created(function ($product) {
            activity()
                ->performedOn($product)
                ->withProperties([
                    'tenant_id'  => request()->user()?->tenant_id,
                    'product_id' => $product->id,
                ])
                ->event(ProductActivityType::Created->value)
                ->log('Product created')
            ;
        });

        static::updated(function ($product) {
            activity()
                ->performedOn($product)
                ->withProperties([
                    'tenant_id'  => request()->user()?->tenant_id,
                    'product_id' => $product->id,
                ])
                ->event(ProductActivityType::Updated->value)
                ->log('Product updated')
            ;
        });

        static::deleted(function ($product) {
            activity()
                ->performedOn($product)
                ->withProperties([
                    'tenant_id'  => request()->user()?->tenant_id,
                    'product_id' => $product->id,
                ])
                ->event(ProductActivityType::Deleted->value)
                ->log('Product deleted')
            ;
        });
    }
}
