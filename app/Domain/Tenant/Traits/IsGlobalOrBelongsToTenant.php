<?php

namespace App\Domain\Tenant\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Scopes\GlobalOrCurrentTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

trait IsGlobalOrBelongsToTenant
{
    /**
     * @throws TenantNotFoundException
     */
    protected static function bootIsGlobalOrBelongsToTenant(): void
    {
        static::creating(function (Model $model): void {
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

        static::addGlobalScope(new GlobalOrCurrentTenantScope());
    }
}
