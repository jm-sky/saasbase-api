<?php

namespace Tests\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Laravel\Sanctum\Sanctum;

trait WithAuthenticatedUser
{
    protected function authenticateUser(Tenant $tenant): User
    {
        $user = User::factory()->create();
        $user->tenants()->attach($tenant);

        Sanctum::actingAs($user);

        return $user;
    }
}
