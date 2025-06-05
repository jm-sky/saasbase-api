<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserIdentityDocumentRevision extends BaseModel
{
    protected $fillable = [
        'document_id',
        'number',
        'country',
        'issued_at',
        'expires_at',
        'changed_at',
        'changed_by',
    ];

    protected $casts = [
        'issued_at'  => 'date',
        'expires_at' => 'date',
        'changed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(UserIdentityDocument::class, 'document_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
