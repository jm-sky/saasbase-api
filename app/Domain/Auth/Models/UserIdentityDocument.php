<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UserIdentityDocument extends BaseModel implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'type',
        'number',
        'country',
        'issued_at',
        'expires_at',
        'is_verified',
        'verified_at',
        'verified_by',
        'meta',
    ];

    protected $casts = [
        'issued_at'   => 'date',
        'expires_at'  => 'date',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'meta'        => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(UserIdentityDocumentRevision::class, 'document_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document_images')
            ->singleFile()
        ;
    }
}
