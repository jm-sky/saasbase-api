<?php

namespace Tests\Traits;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;

trait WithAuthenticatedUser
{
    protected function authenticateUser(Tenant $tenant, ?User $user = null): User
    {
        $user = $user ?? User::factory()->create();
        $user->tenants()->attach($tenant);

        // Generate a JWT token for the user
        $token = JwtHelper::createTokenWithTenant($user, $tenant->id);

        // Set the token in the Authorization header for the request
        $this->withHeader('Authorization', 'Bearer ' . $token);

        return $user;
    }
}
