<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Traits\HaveAddresses;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string      $id
 * @property string      $name
 * @property string      $slug
 * @property string|null $owner_id
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property ?Carbon     $deleted_at
 * @property ?User       $owner
 */
class Tenant extends BaseModel
{
    use HasUuids;
    use SoftDeletes;
    use HaveAddresses;

    public static ?string $PUBLIC_TENANT_ID = null;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
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

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
