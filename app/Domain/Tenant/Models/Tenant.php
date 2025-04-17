<?php

namespace App\Domain\Tenant\Models;

use Carbon\Carbon;
use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 */
class Tenant extends BaseModel
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_users')
            ->withPivot(['role'])
            ->withTimestamps();
    }
}
