<?php

namespace App\Domain\Common\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasTags;
use App\Domain\Common\Traits\HaveAddresses;
use App\Domain\Common\Traits\IsSearchable;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $first_name
 * @property string $last_name
 * @property string $position
 * @property string $email
 * @property string $phone_number
 * @property string $emails
 * @property string $phone_numbers
 * @property string $notes
 * @property string $user_id
 * @property string $contactable_id
 * @property string $contactable_type
 * @property ?User  $user
 * @property ?Model $contactable
 */
class Contact extends BaseModel implements HasMedia
{
    use SoftDeletes;
    use BelongsToTenant;
    use InteractsWithMedia;
    use HasTags;
    use HaveAddresses;
    use HasActivityLog;
    use HasActivityLogging;
    use IsSearchable;

    protected $fillable = [
        'first_name',
        'last_name',
        'position',
        'email',
        'phone_number',
        'emails',
        'phone_numbers',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'emails'        => 'array',
        'phone_numbers' => 'array',
    ];

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']))
        ;
    }

    protected static function newFactory()
    {
        return ContactFactory::new();
    }
}
