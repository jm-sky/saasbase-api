<?php

namespace App\Domain\Tenant\Support;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class TenantIdResolver
{
    /**
     * @throws TenantNotFoundException
     */
    public static function resolve(): string
    {
        try {
            /** @var ?User $user */
            $user = Auth::user();

            return $user?->getTenantId() ?? Tenant::$BYPASSED_TENANT_ID;
        } catch (JWTException) {
            throw new TenantNotFoundException();
        }
    }
}
