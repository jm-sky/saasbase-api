<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\Position;
use Illuminate\Support\Facades\DB;

class OrganizationPositionService
{
    public function createSpecialUnits(): array
    {
        $awaiting = OrganizationUnit::firstOrCreate([
            'name' => 'Awaiting Assignment',
            'code' => 'awaiting-assignment',
        ], [
            'description' => 'Temporary unit for new users awaiting assignment',
            'is_active'   => true,
        ]);

        $inactive = OrganizationUnit::firstOrCreate([
            'name' => 'Not Working Anymore',
            'code' => 'not-working-anymore',
        ], [
            'description' => 'Unit for inactive users',
            'is_active'   => false,
        ]);

        return [$awaiting, $inactive];
    }

    public function assignUserToPosition(
        User $user,
        OrganizationUnit $unit,
        ?Position $position = null,
        array $options = []
    ): void {
        DB::transaction(function () use ($user, $unit, $position, $options) {
            // Remove from awaiting if exists
            $this->removeFromAwaitingAssignment($user);

            // Assign to unit with position
            $user->assignToPosition($unit, $position, $options);
        });
    }

    public function removeFromAwaitingAssignment(User $user): void
    {
        $awaitingUnit = OrganizationUnit::where('code', 'awaiting-assignment')->first();

        if ($awaitingUnit) {
            $this->removeUserFromUnit($user, $awaitingUnit);
        }
    }

    public function removeUserFromUnit(User $user, OrganizationUnit $unit, $endDate = null): void
    {
        $endDate = $endDate ?? now()->toDateString();

        $orgUnitUser = $user->orgUnitUsers()
            ->where('organization_unit_id', $unit->id)
            ->whereNull('end_date')
            ->whereNull('valid_until')
            ->first()
        ;

        if ($orgUnitUser) {
            $orgUnitUser->update([
                'end_date'    => $endDate,
                'valid_until' => now(),
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

    public function moveToInactive(User $user): void
    {
        DB::transaction(function () use ($user) {
            // End all current assignments
            $user->orgUnitUsers()
                ->active()
                ->update([
                    'end_date'    => now()->toDateString(),
                    'valid_until' => now(),
                ])
            ;

            // Remove all current roles
            $user->syncRoles([]);

            // Move to inactive unit
            $inactiveUnit = OrganizationUnit::where('code', 'not-working-anymore')->first();

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
            $query->active()
                ->whereHas('position', function ($q) {
                    $q->where('is_learning', true);
                })
            ;
        })->with(['orgUnitUsers.organizationUnit', 'orgUnitUsers.position'])->get();
    }

    public function getOrganizationChart()
    {
        return OrganizationUnit::with([
            'positions.category',
            'orgUnitUsers.user',
            'orgUnitUsers.position',
        ])->get()->map(function ($unit) {
            return [
                'unit'                 => $unit,
                'users_with_positions' => $unit->getUsersWithPositions(),
                'positions'            => $unit->positions->map(function ($position) {
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
