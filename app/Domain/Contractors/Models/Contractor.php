<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\Comment;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Traits\HasMediaSignedUrls;
use App\Domain\Common\Traits\HasTags;
use App\Domain\Common\Traits\HaveAddresses;
use App\Domain\Common\Traits\HaveBankAccounts;
use App\Domain\Common\Traits\HaveComments;
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Database\Factories\ContractorFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

/**
 * @property string                   $id
 * @property string                   $tenant_id
 * @property string                   $name
 * @property string                   $email
 * @property ?string                  $phone
 * @property ?string                  $website
 * @property ?string                  $country
 * @property ?string                  $tax_id
 * @property ?string                  $description
 * @property bool                     $is_active
 * @property bool                     $is_buyer
 * @property bool                     $is_supplier
 * @property Carbon                   $created_at
 * @property Carbon                   $updated_at
 * @property ?Carbon                  $deleted_at
 * @property Collection|Address[]     $addresses
 * @property Collection|BankAccount[] $bankAccounts
 * @property Collection|Comment[]     $comments
 * @property Collection|Media[]       $media
 * @property Collection|string[]      $tags
 */
class Contractor extends BaseModel implements HasMedia
{
    use SoftDeletes;
    use BelongsToTenant;
    use InteractsWithMedia;
    use HasMediaSignedUrls;
    use HaveAddresses;
    use HaveBankAccounts;
    use HaveComments;
    use HasTags;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'country',
        'tax_id',
        'description',
        'is_active',
        'is_buyer',
        'is_supplier',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_buyer'    => 'boolean',
        'is_supplier' => 'boolean',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(ContractorContactPerson::class);
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
            ->width(config('domains.contractors.logo.size', 256))
            ->height(config('domains.contractors.logo.size', 256))
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

    protected static function newFactory()
    {
        return ContractorFactory::new();
    }
}
