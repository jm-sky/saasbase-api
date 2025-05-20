<?php

namespace App\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserIdentityDocumentRevision extends Model
{
    use HasUuids;

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
