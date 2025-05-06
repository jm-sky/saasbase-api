<?php

namespace App\Domain\Tenant\Listeners;

use App\Domain\Auth\Events\UserCreated;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
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

        $rootUnit = OrganizationUnit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'parent_id' => null],
            [
                'id'         => (string) Str::uuid(),
                'name'       => $tenant->name,
                'short_name' => Str::slug($tenant->name),
            ]
        );

        OrgUnitUser::firstOrCreate(
            [
                'organization_unit_id' => $rootUnit->id,
                'user_id'              => $event->user->id,
            ],
            [
                'id'   => (string) Str::uuid(),
                'role' => OrgUnitRole::Owner,
            ]
        );
    }
}
