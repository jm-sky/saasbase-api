<?php

namespace App\Domain\Tenant\Scopes;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Global scope that filters records to show either global records (tenant_id is null)
 * or records belonging to the current tenant.
 */
class GlobalOrCurrentTenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @var ?User $user */
        $user     = Auth::user();
        $tenantId = $user?->getTenantId() ?? Tenant::$BYPASSED_TENANT_ID;

        $table = $model->getTable();

        $builder->where(function ($query) use ($tenantId, $table): void {
            $query->where("{$table}.tenant_id", Tenant::GLOBAL_TENANT_ID)
                ->orWhere("{$table}.tenant_id", $tenantId)
            ;
        });
    }
}
