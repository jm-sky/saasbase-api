<?php

namespace App\Domain\EDoreczenia\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Models\Media;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string             $id
 * @property string             $tenant_id
 * @property string             $message_id
 * @property string             $file_path
 * @property string             $file_name
 * @property int                $file_size
 * @property string             $mime_type
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property Carbon             $deleted_at
 * @property EDoreczeniaMessage $message
 * @property Collection<Media>  $media
 *
 * In this domain, each EDoreczeniaMessageAttachment represents a single file attachment for an e-Doręczenia message.
 * File storage and retrieval is handled via Spatie Media Library. Each attachment has exactly one media file (in the 'attachment' collection).
 * This allows us to leverage Media Library features while keeping the domain model explicit and clear.
 */
class EDoreczeniaMessageAttachment extends BaseModel implements HasMedia
{
    use BelongsToTenant;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'tenant_id',
        'message_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(EDoreczeniaMessage::class);
    }

    /**
     * Register the media collection for this attachment.
     * Each attachment can have only one file (singleFile enforced).
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachment')->singleFile();
    }
}
