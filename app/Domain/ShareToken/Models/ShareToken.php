<?php

namespace App\Domain\ShareToken\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string  $id
 * @property string  $token
 * @property string  $shareable_type
 * @property string  $shareable_id
 * @property bool    $only_for_authenticated
 * @property ?Carbon $expires_at
 * @property ?Carbon $last_used_at
 * @property int     $usage_count
 * @property ?int    $max_usage
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class ShareToken extends BaseModel
{
    protected $fillable = [
        'token',
        'shareable_type',
        'shareable_id',
        'only_for_authenticated',
        'expires_at',
        'last_used_at',
        'usage_count',
        'max_usage',
    ];

    protected $casts = [
        'only_for_authenticated' => 'boolean',
        'expires_at'             => 'datetime',
        'last_used_at'           => 'datetime',
        'usage_count'            => 'integer',
        'max_usage'              => 'integer',
    ];

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }
}
