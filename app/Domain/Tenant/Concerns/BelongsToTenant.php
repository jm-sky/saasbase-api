<?php

namespace App\Domain\Tenant\Concerns;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function getCurrentTenantId(): ?string
    {
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

        // Try user's current tenant
        if (Auth::check()) {
            return $user->getTenantId();
        }

        return null;
    }

    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (!$model->tenant_id) {
                $model->tenant_id = static::getCurrentTenantId();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = static::getCurrentTenantId();
            if ($tenantId) {
                $builder->where('tenant_id', $tenantId);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
}
