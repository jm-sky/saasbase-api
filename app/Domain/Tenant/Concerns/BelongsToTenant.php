<?php

namespace App\Domain\Tenant\Concerns;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Trait BelongsToTenant.
 *
 * This trait implements multi-tenancy at the model level by:
 * 1. Automatically scoping queries to the current tenant
 * 2. Ensuring new records are assigned to the current tenant
 * 3. Preventing access to records from other tenants
 *
 * Usage:
 * ```php
 * use BelongsToTenant;
 * ```
 *
 * To temporarily disable tenant scoping:
 * ```php
 * Model::withoutTenantScope(function () {
 *     // Queries here will not be scoped to tenant
 * });
 * ```
 */
trait BelongsToTenant
{
    /**
     * Whether the tenant scope is currently bypassed.
     */
    private static bool $bypassTenantScope = false;

    /**
     * Get the current tenant ID from various sources.
     *
     * Priority:
     * 1. JWT token claims
     * 2. Session
     * 3. User's first tenant
     *
     * @throws TenantNotFoundException
     */
    protected static function getCurrentTenantId(): string
    {
        if (static::$bypassTenantScope) {
            return '';
        }

        // First try to get from JWT token if available
        if (Auth::check() && Auth::payload()?->get('tenant_id')) {
            return Auth::payload()->get('tenant_id');
        }

        // Then try session
        if (Session::has('current_tenant_id')) {
            return Session::get('current_tenant_id');
        }

        /** @var User|null $user */
        $user = Auth::user();

        // Finally, try user's first tenant
        if ($user && $user->tenants()->first()?->id) {
            $tenantId = $user->tenants()->first()->id;
            Session::put('current_tenant_id', $tenantId);

            return $tenantId;
        }

        throw new TenantNotFoundException();
    }

    /**
     * Boot the trait.
     *
     * This method:
     * 1. Adds a global scope to filter by tenant
     * 2. Ensures new records are assigned to current tenant
     */
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (!$model->tenant_id) {
                $model->tenant_id = static::getCurrentTenantId();
            } elseif (!static::$bypassTenantScope && $model->tenant_id !== static::getCurrentTenantId()) {
                throw new \RuntimeException('Cannot create record for different tenant');
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (!static::$bypassTenantScope) {
                $tenantId = static::getCurrentTenantId();
                $builder->where('tenant_id', $tenantId);
            }
        });
    }

    /**
     * Define the belongs to relationship to Tenant model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Execute a callback without tenant scoping.
     *
     * @param callable $callback The callback to execute
     *
     * @return mixed The callback result
     */
    public static function withoutTenantScope(callable $callback): mixed
    {
        static::$bypassTenantScope = true;

        try {
            return $callback();
        } finally {
            static::$bypassTenantScope = false;
        }
    }

    /**
     * Disable the tenant scope for the query.
     */
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
