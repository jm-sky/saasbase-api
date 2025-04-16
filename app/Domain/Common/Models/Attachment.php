<?php

namespace App\Domain\Common\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $file_name
 * @property string $file_url
 * @property string $file_type
 * @property int $file_size
 * @property string $attachmentable_id
 * @property string $attachmentable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 *
 * @property-read Model $attachmentable
 */
class Attachment extends BaseModel
{
    protected $fillable = [
        'file_name',
        'file_url',
        'file_type',
        'file_size',
        'attachmentable_id',
        'attachmentable_type',
    ];

    protected array $casts = [
        'file_size' => 'integer',
    ];

    public function attachmentable(): MorphTo
    {
        return $this->morphTo();
    }
}
