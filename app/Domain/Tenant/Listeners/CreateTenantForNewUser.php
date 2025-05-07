<?php

namespace App\Domain\Tenant\Listeners;

use App\Domain\Auth\Events\UserCreated;
use App\Domain\Tenant\Actions\InitializeTenantDefaults;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;

class CreateTenantForNewUser
{
    public function handle(UserCreated $event): void
    {
        if (!config('users.registration.create_tenant')) {
            return;
        }

        $tenant = Tenant::create([
            'name'     => "{$event->user->first_name}'s workspace",
            'slug'     => Str::slug(Str::before($event->user->email, '@')),
            'owner_id' => $event->user->id,
        ]);

        $event->user->tenants()->attach($tenant, ['role' => 'admin']);

        (new InitializeTenantDefaults())->execute($tenant, $event->user);
    }
}
