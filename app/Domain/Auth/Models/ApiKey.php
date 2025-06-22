<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string  $id
 * @property string  $tenant_id
 * @property string  $user_id
 * @property string  $name
 * @property string  $key
 * @property array   $scopes
 * @property bool    $is_active
 * @property ?Carbon $last_used_at
 * @property ?Carbon $expires_at
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?Carbon $deleted_at
 * @property Tenant  $tenant
 * @property User    $user
 */
class ApiKey extends BaseModel
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'key',
        'scopes',
        'is_active',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'scopes'       => 'array',
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
