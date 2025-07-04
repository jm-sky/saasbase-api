<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Tenant\Enums\DefaultPositionCategory;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
use App\Domain\Tenant\Models\Position;
use App\Domain\Tenant\Models\PositionCategory;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;

class CreateRootOrganizationUnit
{
    public static function createUnit(Tenant $tenant): OrganizationUnit
    {
        return OrganizationUnit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'parent_id' => null],
            [
                'id'         => (string) Str::ulid(),
                'name'       => $tenant->name,
                'code'       => Str::slug($tenant->name),
            ]
        );
    }

    public static function createOwner(Tenant $tenant, OrganizationUnit $rootUnit): void
    {
        OrgUnitUser::firstOrCreate(
            [
                'organization_unit_id' => $rootUnit->id,
                'user_id'              => $tenant->owner_id,
            ],
            [
                'id'   => (string) Str::ulid(),
                'role' => OrgUnitRole::Owner,
            ]
        );
    }

    public static function createPositions(OrganizationUnit $rootUnit): void
    {
        $directorCategory = PositionCategory::where('name', DefaultPositionCategory::Director->value)->first();

        if (!$directorCategory) {
            return;
        }

        Position::firstOrCreate(
            ['organization_unit_id' => $rootUnit->id],
            [
                'id'                   => (string) Str::ulid(),
                'name'                 => 'Owner',
                'is_active'            => true,
                'is_director'          => true,
                'is_learning'          => false,
                'position_category_id' => $directorCategory->id,
            ]
        );
    }
}
