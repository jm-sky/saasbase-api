<?php

namespace App\Domain\Tenant\Listeners;

use App\Domain\Auth\Events\UserCreated;
use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Actions\InitializeTenantDefaults;
use App\Domain\Tenant\Enums\UserTenantRole;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;

class CreateTenantForNewUser
{
    public static bool $BYPASSED = false;

    public function handle(UserCreated $event): void
    {
        if (self::$BYPASSED) {
            return;
        }

        if (!config('users.registration.create_tenant')) {
            return;
        }

        $tenant = Tenant::create(self::prepareTenantData($event->user));

        $event->user->tenants()->attach($tenant, ['role' => UserTenantRole::Admin->value]);

        (new InitializeTenantDefaults())->execute($tenant, $event->user);
    }

    public static function prepareTenantData(User $user): array
    {
        return [
            'name'     => "{$user->first_name}'s workspace",
            'slug'     => Str::slug(Str::before($user->email, '@')),
            'owner_id' => $user->id,
        ];
    }
}
