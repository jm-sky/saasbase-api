<?php

namespace App\Domain\Common\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * @property string      $id
 * @property ?string     $tenant_id
 * @property ?string     $log_name
 * @property string      $description
 * @property ?string     $subject_type
 * @property ?int        $subject_id
 * @property ?string     $causer_type
 * @property ?int        $causer_id
 * @property ?string     $event
 * @property ?string     $batch_uuid
 * @property ?Collection $properties
 * @property ?Carbon     $created_at
 * @property ?Carbon     $updated_at
 * @property ?Model      $causer
 * @property Collection  $changes
 * @property ?Model      $subject
 */
class Activity extends BaseActivity
{
    use HasUlids;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'event',
        'batch_uuid',
        'properties',
        'tenant_id',
    ];

    protected $casts = [
        'properties' => 'collection',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $activity) {
            $activity->tenant_id = $activity->tenant_id ?? request()->user()?->getTenantId();
        });
    }
}
