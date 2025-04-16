<?php

namespace App\Domain\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property string $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class BaseModel extends Model
{
    use HasUuids;

    // protected string $keyType = 'string';
    // public bool $incrementing = false;

    protected array $guarded = ['id'];
}
