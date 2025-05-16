<?php

namespace App\Domain\Common\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * @property string                                             $id
 * @property ?string                                            $log_name
 * @property string                                             $description
 * @property ?string                                            $subject_type
 * @property ?int                                               $subject_id
 * @property ?string                                            $causer_type
 * @property ?int                                               $causer_id
 * @property ?string                                            $event
 * @property ?string                                            $batch_uuid
 * @property ?Collection                                        $properties
 * @property ?Carbon                                            $created_at
 * @property ?Carbon                                            $updated_at
 * @property \Illuminate\Database\Eloquent\Model|\Eloquent|null $causer
 * @property Collection                                         $changes
 * @property \Illuminate\Database\Eloquent\Model|\Eloquent|null $subject
 */
class Activity extends BaseActivity
{
    use HasUuids;
}
