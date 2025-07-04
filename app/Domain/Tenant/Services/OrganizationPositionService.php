<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Actions\CreateTechnicalOrganizationUnits;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
use App\Domain\Tenant\Models\Position;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Support\TenantIdResolver;
use Illuminate\Support\Facades\DB;

class OrganizationPositionService
{
    private Tenant $tenant;

    public function __construct(?Tenant $tenant = null)
    {
        $this->tenant = $tenant ?? Tenant::find(TenantIdResolver::resolve());
    }

    public function createSpecialUnits(): array
    {
        $unassigned = CreateTechnicalOrganizationUnits::createUnassignedUnit($this->tenant, $this->tenant->rootOrganizationUnit);
        $inactive   = CreateTechnicalOrganizationUnits::createFormerEmployeesUnit($this->tenant, $this->tenant->rootOrganizationUnit);

        return [$unassigned, $inactive];
    }

    public function assignUserToPosition(
        User $user,
        OrganizationUnit $unit,
        ?Position $position = null,
        array $options = []
    ): void {
        DB::transaction(function () use ($user, $unit, $position, $options) {
            // Remove from awaiting if exists
            $this->removeFromUnassignedUnit($user);

            // Assign to unit with position
            $user->assignToPosition($unit, $position, $options);
        });
    }

    public function removeFromUnassignedUnit(User $user): void
    {
        $unassignedUnit = $this->tenant->unassignedOrganizationUnit;

        if ($unassignedUnit) {
            $this->removeUserFromUnit($user, $unassignedUnit);
        }
    }

    public function removeUserFromUnit(User $user, OrganizationUnit $unit, $validUntil = null): void
    {
        $validUntil = $validUntil ?? now();

        /** @var ?OrgUnitUser $orgUnitUser */
        $orgUnitUser = $user->orgUnitUsers()
            ->where('organization_unit_id', $unit->id)
            ->whereNull('valid_until')
            ->first()
        ;

        if ($orgUnitUser) {
            $orgUnitUser->update([
                'valid_until' => $validUntil,
            ]);

            // Remove role if no other positions have same role
            if ($orgUnitUser->position && $orgUnitUser->position->role_name) {
                $hasOtherPositionsWithRole = $user->currentPositions()
                    ->where('role_name', $orgUnitUser->position->role_name)
                    ->where('id', '!=', $orgUnitUser->position->id)
                    ->exists()
                ;

                if (!$hasOtherPositionsWithRole) {
                    $user->removeRole($orgUnitUser->position->role_name);
                }
            }
        }
    }

    public function moveToFormerEmployees(User $user): void
    {
        DB::transaction(function () use ($user) {
            // @phpstan-ignore-next-line
            $user->orgUnitUsers()->active()
                ->update([
                    'valid_until' => now(),
                ])
            ;

            // Remove all current roles
            $user->syncRoles([]);

            // Move to inactive unit
            $inactiveUnit = $this->tenant->formerEmployeesOrganizationUnit;

            if ($inactiveUnit) {
                $user->assignToPosition($inactiveUnit, null, [
                    'is_primary' => true,
                    'role'       => OrgUnitRole::Employee,
                ]);
            }
        });
    }

    public function getAllDirectors()
    {
        return User::whereHas('orgUnitUsers', function ($query) {
            /* @phpstan-ignore-next-line */
            $query->active()
                ->whereHas('position', function ($q) {
                    $q->where('is_director', true);
                })
            ;
        })->with(['orgUnitUsers.organizationUnit', 'orgUnitUsers.position'])->get();
    }

    public function getAllLearningPositions()
    {
        return User::whereHas('orgUnitUsers', function ($query) {
            /* @phpstan-ignore-next-line */
            $query->active()
                ->whereHas('position', function ($q) {
                    $q->where('is_learning', true);
                })
            ;
        })->with(['orgUnitUsers.organizationUnit', 'orgUnitUsers.position'])->get();
    }

    public function getOrganizationChart()
    {
        $units = OrganizationUnit::with([
            'positions.category',
            'orgUnitUsers.user',
            'orgUnitUsers.position',
        ])->get();

        return $units->map(function (OrganizationUnit $unit) {
            return [
                'unit'                 => $unit,
                'users_with_positions' => $unit->getUsersWithPositions(),
                'positions'            => $unit->positions->map(function (Position $position) {
                    return [
                        'position'            => $position,
                        'category'            => $position->category,
                        'current_users_count' => $position->currentUsers()->count(),
                    ];
                }),
            ];
        });
    }
}
