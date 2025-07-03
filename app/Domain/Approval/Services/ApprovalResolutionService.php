<?php

namespace App\Domain\Approval\Services;

use App\Domain\Approval\Enums\ApproverType;
use App\Domain\Approval\Models\ApprovalStepApprover;
use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ApprovalResolutionService
{
    /**
     * Resolve all approvers for a given approval step approver configuration.
     */
    public function resolveApprovers(ApprovalStepApprover $stepApprover, Expense $expense): Collection
    {
        return match ($stepApprover->approver_type) {
            ApproverType::USER              => $this->resolveUserApprover($stepApprover),
            ApproverType::UNIT_ROLE         => $this->resolveUnitRoleApprover($stepApprover, $expense),
            ApproverType::SYSTEM_PERMISSION => $this->resolveSystemPermissionApprover($stepApprover),
            default                         => new Collection(),
        };
    }

    /**
     * Resolve specific user approver.
     */
    private function resolveUserApprover(ApprovalStepApprover $stepApprover): Collection
    {
        if (!$stepApprover->approver_value) {
            Log::warning('User approver configuration missing approver_value', [
                'step_approver_id' => $stepApprover->id,
            ]);

            return new Collection();
        }

        $user = User::find($stepApprover->approver_value);

        if (!$user) {
            Log::warning('User approver not found', [
                'step_approver_id' => $stepApprover->id,
                'user_id'          => $stepApprover->approver_value,
            ]);

            return new Collection();
        }

        // Check if user is active (assuming an active field exists)
        if (method_exists($user, 'isActive') && !$user->isActive()) {
            Log::info('User approver is inactive', [
                'step_approver_id' => $stepApprover->id,
                'user_id'          => $stepApprover->approver_value,
            ]);

            return new Collection();
        }

        return new Collection([$user]);
    }

    /**
     * Resolve unit role approvers based on organizational hierarchy.
     */
    private function resolveUnitRoleApprover(ApprovalStepApprover $stepApprover, Expense $expense): Collection
    {
        // Get the expense creator's organizational context
        $expenseCreator = $expense->createdByUser;

        if (!$expenseCreator) {
            Log::warning('Expense has no creator for unit role resolution', [
                'expense_id'       => $expense->id,
                'step_approver_id' => $stepApprover->id,
            ]);

            return new Collection();
        }

        // Get the user's primary organizational unit
        $primaryUnit = $this->getUserPrimaryUnit($expenseCreator);

        if (!$primaryUnit) {
            Log::warning('User has no primary organizational unit', [
                'user_id'          => $expenseCreator->id,
                'step_approver_id' => $stepApprover->id,
            ]);

            return new Collection();
        }

        // Determine target unit based on approver configuration
        $targetUnit = $this->resolveTargetUnit($primaryUnit, $stepApprover);

        if (!$targetUnit) {
            Log::warning('Could not resolve target unit for approval', [
                'primary_unit_id'  => $primaryUnit->id,
                'step_approver_id' => $stepApprover->id,
                'unit_role'        => $stepApprover->approver_value,
            ]);

            return new Collection();
        }

        // Find users with the required role in the target unit
        return $this->getUsersWithRoleInUnit($targetUnit, $stepApprover->approver_value);
    }

    /**
     * Resolve system permission approvers.
     */
    private function resolveSystemPermissionApprover(ApprovalStepApprover $stepApprover): Collection
    {
        if (!$stepApprover->approver_value) {
            Log::warning('System permission approver configuration missing permission', [
                'step_approver_id' => $stepApprover->id,
            ]);

            return new Collection();
        }

        // Find all users with the specified system permission
        // This assumes a permission system exists - adjust based on actual implementation
        return User::whereHas('permissions', function ($query) use ($stepApprover) {
            $query->where('name', $stepApprover->approver_value);
        })->get();
    }

    /**
     * Get user's primary organizational unit.
     */
    private function getUserPrimaryUnit($user)
    {
        // This method should return the user's primary organizational unit
        // Implementation depends on the actual organizational structure

        // Check if user has organizational unit memberships
        if (!method_exists($user, 'organizationUnitMemberships')) {
            return null;
        }

        // Get the primary membership (assuming there's a way to identify primary)
        $primaryMembership = $user->organizationUnitMemberships()
            ->where('is_primary', true)
            ->first()
        ;

        if (!$primaryMembership) {
            // Fallback: get the first active membership
            $primaryMembership = $user->organizationUnitMemberships()
                ->whereNull('end_date')
                ->orWhere('end_date', '>', now())
                ->first()
            ;
        }

        return $primaryMembership?->organizationUnit;
    }

    /**
     * Resolve target unit based on approver configuration.
     */
    private function resolveTargetUnit($primaryUnit, ApprovalStepApprover $stepApprover)
    {
        // Handle specific unit ID
        if ($stepApprover->organization_unit_id) {
            // Find the specific organizational unit
            $unitClass = get_class($primaryUnit);

            if (false === $unitClass) {
                return null;
            }

            return $unitClass::find($stepApprover->organization_unit_id);
        }

        // Handle special cases in approver_value (role)
        if ('PARENT_UNIT' === $stepApprover->approver_value) {
            return $primaryUnit->parent;
        }

        // Default: use the user's primary unit
        return $primaryUnit;
    }

    /**
     * Get users with specific role in organizational unit.
     */
    private function getUsersWithRoleInUnit($unit, string $role): Collection
    {
        if (!method_exists($unit, 'memberships')) {
            Log::warning('Organizational unit does not have memberships relationship', [
                'unit_id' => $unit->id,
                'role'    => $role,
            ]);

            return new Collection();
        }

        // Get active memberships with the specified role
        $memberships = $unit->memberships()
            ->where('role', $role)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now())
                ;
            })
            ->with('user')
            ->get()
        ;

        return $memberships->map(function ($membership) {
            return $membership->user;
        })->filter();
    }

    /**
     * Check if user has specific system permission.
     */
    public function userHasSystemPermission(User $user, string $permission): bool
    {
        if (!method_exists($user, 'hasPermissionTo')) {
            return false;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Get all available approvers for an expense across all workflow steps.
     */
    public function getAllAvailableApprovers(Expense $expense): Collection
    {
        $allApprovers = new Collection();

        // This would be called from the workflow execution context
        // where we have access to the workflow and its steps

        return $allApprovers->unique('id');
    }
}
