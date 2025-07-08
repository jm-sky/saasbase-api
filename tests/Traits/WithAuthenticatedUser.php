<?php

namespace Tests\Traits;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Tenant\Models\Tenant;

trait WithAuthenticatedUser
{
    protected function authenticateUser(?Tenant $tenant = null, ?User $user = null): User
    {
        $user = $user ?? User::factory()->create();

        if (!$tenant) {
            $token = JwtHelper::createTokenWithoutTenant($user);
        } else {
            $user->tenants()->attach($tenant, ['role' => RoleName::Admin->value]);
            $token = JwtHelper::createTokenWithTenant($user, $tenant->id);
        }

        $this->withHeader('Authorization', 'Bearer ' . $token);

        return $user;
    }
}
