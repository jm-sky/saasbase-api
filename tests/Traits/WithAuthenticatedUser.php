<?php

namespace Tests\Traits;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

trait WithAuthenticatedUser
{
    protected function authenticateUser(Tenant $tenant): User
    {
        $user = User::factory()->create();
        $user->tenants()->attach($tenant);

        // Generate a JWT token for the user
        $token = JWTAuth::fromUser($user);

        // Set the token in the Authorization header for the request
        $this->withHeader('Authorization', 'Bearer ' . $token);

        // Store the tenant in the session
        Session::put('current_tenant_id', $tenant->id);

        return $user;
    }
}
