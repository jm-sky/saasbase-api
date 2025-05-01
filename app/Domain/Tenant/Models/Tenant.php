<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string  $id
 * @property string  $name
 * @property string  $slug
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?Carbon $deleted_at
 */
class Tenant extends BaseModel
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tenants')
            ->using(UserTenant::class)
            ->withPivot(['role'])
            ->withTimestamps()
        ;
    }
}
