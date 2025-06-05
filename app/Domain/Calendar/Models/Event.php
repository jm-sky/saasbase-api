<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string  $id
 * @property string  $tenant_id
 * @property string  $title
 * @property ?string $description
 * @property Carbon  $start_at
 * @property Carbon  $end_at
 * @property bool    $is_all_day
 * @property ?string $location
 * @property ?string $color
 * @property string  $status
 * @property string  $visibility
 * @property string  $timezone
 * @property ?string $recurrence_rule
 * @property ?array  $reminder_settings
 * @property string  $created_by_id
 * @property ?string $related_type
 * @property ?string $related_id
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class Event extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'is_all_day',
        'location',
        'color',
        'status',
        'visibility',
        'timezone',
        'recurrence_rule',
        'reminder_settings',
        'created_by_id',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'start_at'          => 'datetime',
        'end_at'            => 'datetime',
        'is_all_day'        => 'boolean',
        'reminder_settings' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(EventReminder::class);
    }
}
