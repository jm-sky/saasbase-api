<?php

namespace App\Domain\Utils\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RegistryConfirmation extends BaseModel
{
    protected $fillable = [
        'id',
        'confirmable_id',
        'confirmable_type',
        'type',
        'payload',
        'result',
        'success',
        'checked_at',
    ];

    protected $casts = [
        'payload'    => 'array',
        'result'     => 'array',
        'success'    => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function confirmable(): MorphTo
    {
        return $this->morphTo();
    }
}
