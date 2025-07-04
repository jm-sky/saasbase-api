<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Enums\TechnicalOrganizationUnit;
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
                'code'         => TechnicalOrganizationUnit::Unassigned->value,
                'parent_id'    => $rootUnit->id,
                'is_technical' => true,
            ],
            [
                'id'         => (string) Str::ulid(),
                'name'       => TechnicalOrganizationUnit::Unassigned->getName(),
                'code'       => TechnicalOrganizationUnit::Unassigned->value,
                'is_active'  => true,
            ]
        );
    }

    public static function createFormerEmployeesUnit(Tenant $tenant, OrganizationUnit $rootUnit): OrganizationUnit
    {
        return OrganizationUnit::firstOrCreate(
            [
                'tenant_id'    => $tenant->id,
                'code'         => TechnicalOrganizationUnit::FormerEmployees->value,
                'parent_id'    => $rootUnit->id,
                'is_technical' => true,
            ],
            [
                'id'         => (string) Str::ulid(),
                'name'       => TechnicalOrganizationUnit::FormerEmployees->getName(),
                'code'       => TechnicalOrganizationUnit::FormerEmployees->value,
                'is_active'  => true,
            ]
        );
    }
}
