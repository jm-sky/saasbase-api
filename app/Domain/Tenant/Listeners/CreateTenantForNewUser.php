<?php

namespace App\Domain\Tenant\Listeners;

use App\Domain\Auth\Events\UserCreated;
use App\Domain\Tenant\Models\Tenant;

class CreateTenantForNewUser
{
    public function handle(UserCreated $event): void
    {
        $tenant = Tenant::create([
            'name' => "{$event->user->first_name}'s workspace",
        ]);

        $event->user->tenants()->attach($tenant, ['role' => 'admin']);
    }
}
