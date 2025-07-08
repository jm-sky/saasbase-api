<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property RoleName $role
 * @property User     $user
 * @property Tenant   $tenant
 */
class UserTenant extends Pivot
{
    use HasUlids;

    protected $table = 'user_tenants';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'tenant_id',
        'role',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role' => 'string',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->user->assignRole($model->role);
        });

        static::deleted(function ($model) {
            $model->user->removeRole($model->role);
        });
    }

    /**
     * Get the user that owns the tenant membership.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant that the user belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
