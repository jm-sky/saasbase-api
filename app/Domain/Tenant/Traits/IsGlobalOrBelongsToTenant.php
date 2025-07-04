<?php

namespace App\Domain\Tenant\Traits;

use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Scopes\GlobalOrCurrentTenantScope;
use App\Domain\Tenant\Support\TenantIdResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait IsGlobalOrBelongsToTenant
{
    /**
     * @throws TenantNotFoundException
     */
    protected static function bootIsGlobalOrBelongsToTenant(): void
    {
        static::creating(function (Model $model): void {
            // @phpstan-ignore-next-line
            if (!$model->tenant_id) {
                $model->tenant_id = TenantIdResolver::resolve();
            }

            if (Tenant::NONE_TENANT_ID === $model->tenant_id) {
                throw new TenantNotFoundException();
            }
        });

        static::addGlobalScope(new GlobalOrCurrentTenantScope());
    }

    /**
     * Disable the tenant scope for the query.
     */
    public static function withoutTenant(): Builder
    {
        return static::withoutGlobalScope(GlobalOrCurrentTenantScope::class);
    }
}
