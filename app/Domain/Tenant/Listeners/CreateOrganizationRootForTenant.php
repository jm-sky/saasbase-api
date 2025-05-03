<?php

namespace App\Domain\Tenant\Listeners;

use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Enums\OrgUnitRole;
use Illuminate\Support\Str;

class CreateOrganizationRootForTenant
{
    public function handle(object $event): void
    {
        $tenant = $event->tenant;
        $user = $event->user;

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
                'user_id'              => $user->id,
            ],
            [
                'id'   => (string) Str::uuid(),
                'role' => OrgUnitRole::CEO,
            ]
        );
    }
}
