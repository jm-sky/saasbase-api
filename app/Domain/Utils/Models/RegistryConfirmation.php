<?php

namespace App\Domain\Utils\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Utils\Enums\RegistryConfirmationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int                        $id
 * @property int                        $confirmable_id
 * @property string                     $confirmable_type
 * @property string                     $type
 * @property array                      $payload
 * @property array                      $result
 * @property RegistryConfirmationStatus $status
 * @property ?Carbon                    $checked_at
 * @property Carbon                     $created_at
 * @property ?Carbon                    $updated_at
 */
class RegistryConfirmation extends BaseModel
{
    protected $fillable = [
        'id',
        'confirmable_id',
        'confirmable_type',
        'type',
        'payload',
        'result',
        'status',
        'checked_at',
    ];

    protected $casts = [
        'payload'    => 'array',
        'result'     => 'array',
        'status'     => RegistryConfirmationStatus::class,
        'checked_at' => 'datetime',
    ];

    public function confirmable(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScopes();
    }
}
