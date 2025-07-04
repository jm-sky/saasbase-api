<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;

class CreateTechnicalOrganizationUnits
{
    public static function createUnassignedUnit(Tenant $tenant, OrganizationUnit $rootUnit): OrganizationUnit
    {
        return OrganizationUnit::firstOrCreate(
            [
                'tenant_id'    => $tenant->id,
                'name'         => 'Unassigned',
                'parent_id'    => $rootUnit->id,
                'is_technical' => true,
            ],
            [
                'id'         => (string) Str::ulid(),
                'name'       => 'Unassigned',
                'code'       => 'unassigned',
            ]
        );
    }

    public static function createFormerEmployeesUnit(Tenant $tenant, OrganizationUnit $rootUnit): OrganizationUnit
    {
        return OrganizationUnit::firstOrCreate(
            [
                'tenant_id'    => $tenant->id,
                'name'         => 'FormerEmployees',
                'parent_id'    => $rootUnit->id,
                'is_technical' => true,
            ],
            [
                'id'         => (string) Str::ulid(),
                'name'       => 'Former Employees',
                'code'       => 'former-employees',
            ]
        );
    }
}
