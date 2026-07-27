<?php

namespace App\Domain\Expense\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->getTenantId() !== null;
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->getTenantId() === $expense->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->getTenantId() !== null;
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->getTenantId() === $expense->tenant_id;
    }

    /**
     * Deleting a financial document is destructive — restricted to
     * tenant Owner/Admin, unlike create/update which any member needs
     * for day-to-day expense processing (OCR correction, allocation,
     * approval start).
     */
    public function delete(User $user, Expense $expense): bool
    {
        if ($user->getTenantId() !== $expense->tenant_id) {
            return false;
        }

        return TenantScopedRoles::userHasAnyRole($user, $expense->tenant_id, [RoleName::Owner->value, RoleName::Admin->value]);
    }
}
