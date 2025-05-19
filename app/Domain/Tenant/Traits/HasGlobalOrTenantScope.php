<?php

namespace App\Domain\Tenant\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Scopes\IsGlobalOrTenants;

trait HasGlobalOrTenantScope
{
    protected static function bootHasGlobalOrTenantScope()
    {
        /** @var ?User $user */
        $user     = auth()->user();
        $tenantId = $user?->getTenantId() ?? Tenant::$BYPASSED_TENANT_ID;

        static::addGlobalScope(new IsGlobalOrTenants($tenantId));
    }
}
