<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string  $id
 * @property string  $event_id
 * @property string  $attendee_type
 * @property string  $attendee_id
 * @property string  $response_status
 * @property ?Carbon $response_at
 * @property ?string $custom_note
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class EventAttendee extends BaseModel
{
    protected $fillable = [
        'event_id',
        'attendee_type',
        'attendee_id',
        'response_status',
        'response_at',
        'custom_note',
    ];

    protected $casts = [
        'response_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function attendee(): MorphTo
    {
        return $this->morphTo();
    }
}
