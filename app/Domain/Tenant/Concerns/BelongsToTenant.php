<?php

namespace App\Domain\Tenant\Concerns;

use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

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
            if (!$model->tenant_id) {
                $model->tenant_id = Auth::user()?->getTenantId();
            }

            if (!$model->tenant_id) {
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
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }
}
