<?php

namespace App\Domain\Tenant\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Trait BelongsToTenant.
 *
 * Adds tenant_id automatically and scopes queries by tenant.
 */
trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            // @phpstan-ignore-next-line
            if (!$model->tenant_id) {
                try {
                    /** @var ?User $user */
                    $user             = Auth::user();
                    $model->tenant_id = $user?->getTenantId() ?? Tenant::$BYPASSED_TENANT_ID;
                } catch (JWTException) {
                    throw new TenantNotFoundException();
                }
            }

            if (Tenant::NONE_TENANT_ID === $model->tenant_id) {
                throw new TenantNotFoundException();
            }
        });

        static::addGlobalScope(new TenantScope());
    }

    /**
     * Define the belongs to relationship to Tenant model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope query to a specific tenant.
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Disable the tenant scope for the query.
     */
    public static function forTenant(string $tenantId): Builder
    {
        return static::withoutGlobalScope(TenantScope::class)->where('tenant_id', $tenantId);
    }

    /**
     * Disable the tenant scope for the query.
     */
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }
}
