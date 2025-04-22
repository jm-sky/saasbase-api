<?php

namespace App\Domain\Tenant\Concerns;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    private static bool $bypassTenantScope = false;

    protected static function getCurrentTenantId(): ?string
    {
        if (static::$bypassTenantScope) {
            return null;
        }

        /** @var \App\Domain\Auth\Models\User $user */
        $user = Auth::user();

        // Try JWT payload if available
        if ($user?->token?->claims()?->get('tenant_id')) {
            return $user->token->claims()->get('tenant_id');
        }

        // Try session
        if (session('current_tenant_id')) {
            return session('current_tenant_id');
        }

        // Try user's first tenant
        if ($user && $user->tenants()->first()?->id) {
            return $user->tenants()->first()->id;
        }

        return null;
    }

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
                if ($tenantId) {
                    $builder->where('tenant_id', $tenantId);
                }
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        if (!static::$bypassTenantScope && $tenantId !== static::getCurrentTenantId()) {
            throw new \RuntimeException('Cannot access data from different tenant');
        }
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Temporarily disable tenant scoping for test data setup.
     * Should only be used in tests!
     */
    public static function withoutTenantScope(callable $callback): mixed
    {
        if (!app()->environment('testing')) {
            throw new \RuntimeException('withoutTenantScope can only be used in testing environment');
        }

        static::$bypassTenantScope = true;
        try {
            return $callback();
        } finally {
            static::$bypassTenantScope = false;
        }
    }
}
