<?php

namespace App\Domain\Tenant\Listeners;

use App\Domain\Auth\Events\UserCreated;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;
use App\Domain\Tenant\Events\TenantCreated;

class CreateTenantForUser
{
    public function handle(UserCreated $event): void
    {
        $user = $event->user;

        $tenant = Tenant::create([
            'id' => Str::uuid(),
            'owner_id' => $user->id,
            'name' => $user->name . "'s Workspace", // adjust as needed
        ]);

        event(new TenantCreated($tenant));
    }
}
