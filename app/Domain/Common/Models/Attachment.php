<?php

namespace App\Domain\Common\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $file_name
 * @property string  $file_url
 * @property string  $file_type
 * @property int     $file_size
 * @property string  $attachmentable_id
 * @property string  $attachmentable_type
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?Carbon $deleted_at
 * @property Model   $attachmentable
 */
class Attachment extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'file_name',
        'file_url',
        'file_type',
        'file_size',
        'attachmentable_id',
        'attachmentable_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    public function attachmentable(): MorphTo
    {
        return $this->morphTo();
    }
}
