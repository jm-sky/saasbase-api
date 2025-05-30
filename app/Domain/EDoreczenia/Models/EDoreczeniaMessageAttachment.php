<?php

namespace App\Domain\EDoreczenia\Models;

use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EDoreczeniaMessageAttachment extends Model
{
    use HasUuids;
    use BelongsToTenant;
    use SoftDeletes;

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
}
