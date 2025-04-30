<?php

namespace App\Domain\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        /** @var ?User $user */
        $user     = Auth::user();
        $tenantId = $user?->getTenantId();

        $builder->where('tenant_id', $tenantId);
    }
}
