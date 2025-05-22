<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $event_id
 * @property string $user_id
 * @property Carbon $reminder_at
 * @property string $reminder_type
 * @property bool   $is_sent
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class EventReminder extends BaseModel
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'user_id',
        'reminder_at',
        'reminder_type',
        'is_sent',
    ];

    protected $casts = [
        'reminder_at' => 'datetime',
        'is_sent'     => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
