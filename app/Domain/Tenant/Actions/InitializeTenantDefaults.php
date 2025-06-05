<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\DefaultMeasurementUnit;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;

class InitializeTenantDefaults
{
    public function execute(Tenant $tenant, ?User $owner = null): void
    {
        $this->createRootOrganizationUnit($tenant, $owner);
        $this->seedDefaultMeasurementUnits($tenant);
        // 3. (Optional) Add more default setup here
    }

    protected function createRootOrganizationUnit(Tenant $tenant, ?User $owner = null): void
    {
        $rootUnit = OrganizationUnit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'parent_id' => null],
            [
                'id'         => (string) Str::ulid(),
                'name'       => $tenant->name,
                'short_name' => Str::slug($tenant->name),
            ]
        );

        if ($owner) {
            OrgUnitUser::firstOrCreate(
                [
                    'organization_unit_id' => $rootUnit->id,
                    'user_id'              => $owner->id,
                ],
                [
                    'id'   => (string) Str::ulid(),
                    'role' => OrgUnitRole::Owner,
                ]
            );
        }
    }

    protected function seedDefaultMeasurementUnits(Tenant $tenant): void
    {
        $defaultUnits = DefaultMeasurementUnit::where('is_default', true)->get();

        foreach ($defaultUnits as $unit) {
            MeasurementUnit::withoutTenant()->firstOrCreate([
                'tenant_id' => $tenant->id,
                'code'      => $unit->code,
                'name'      => $unit->name,
                'category'  => $unit->category,
            ]);
        }
    }
}
