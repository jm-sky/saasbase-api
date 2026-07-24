<?php

namespace App\Domain\Common\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bank accounts.
     */
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant);
    }

    /**
     * Determine whether the user can view the bank account.
     */
    public function view(User $user, BankAccount $bankAccount, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant) && $bankAccount->tenant_id === $tenant->id;
    }

    /**
     * Determine whether the user can create bank accounts. This is the
     * account used to receive payments from clients, so — unlike viewing —
     * it's restricted to Owner/Admin, not any member.
     */
    public function create(User $user, Tenant $tenant): bool
    {
        return $this->isOwnerOrAdmin($user, $tenant);
    }

    /**
     * Determine whether the user can update the bank account.
     */
    public function update(User $user, BankAccount $bankAccount, Tenant $tenant): bool
    {
        return $bankAccount->tenant_id === $tenant->id && $this->isOwnerOrAdmin($user, $tenant);
    }

    /**
     * Determine whether the user can delete the bank account.
     */
    public function delete(User $user, BankAccount $bankAccount, Tenant $tenant): bool
    {
        return $bankAccount->tenant_id === $tenant->id && $this->isOwnerOrAdmin($user, $tenant);
    }

    private function isOwnerOrAdmin(User $user, Tenant $tenant): bool
    {
        if (! $user->isCurrentTenant($tenant)) {
            return false;
        }

        return TenantScopedRoles::userHasAnyRole($user, $tenant->id, [RoleName::Owner->value, RoleName::Admin->value]);
    }
}
