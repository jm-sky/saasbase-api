<?php

namespace App\Domain\Common\Models;

use Carbon\Carbon;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string $category
 * @property bool   $is_default
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DefaultMeasurementUnit extends BaseModel
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'is_default',
    ];
}
