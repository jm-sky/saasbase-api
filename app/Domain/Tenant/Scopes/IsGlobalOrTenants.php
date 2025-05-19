<?php

namespace App\Domain\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IsGlobalOrTenants implements Scope
{
    protected ?string $tenantId;

    public function __construct(?string $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->where(function ($query) {
            $query->whereNull('tenant_id')
                ->orWhere('tenant_id', $this->tenantId)
            ;
        });
    }
}
