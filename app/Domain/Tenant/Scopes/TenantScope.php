<?php

namespace App\Domain\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = auth()->user()?->getTenantId();

        $builder->where('tenant_id', $tenantId);
    }
}
