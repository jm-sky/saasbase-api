<?php

namespace App\Domain\Common\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Address;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any addresses.
     */
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant);
    }

    /**
     * Determine whether the user can view the address.
     */
    public function view(User $user, Address $address, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant) && $address->tenant_id === $tenant->id;
    }

    /**
     * Determine whether the user can create addresses. The tenant's own
     * official address is used on invoices/legal documents, so — unlike
     * viewing — this is restricted to Owner/Admin, not any member.
     */
    public function create(User $user, Tenant $tenant): bool
    {
        return $this->isOwnerOrAdmin($user, $tenant);
    }

    /**
     * Determine whether the user can update the address.
     */
    public function update(User $user, Address $address, Tenant $tenant): bool
    {
        return $address->tenant_id === $tenant->id && $this->isOwnerOrAdmin($user, $tenant);
    }

    /**
     * Determine whether the user can delete the address.
     */
    public function delete(User $user, Address $address, Tenant $tenant): bool
    {
        return $address->tenant_id === $tenant->id && $this->isOwnerOrAdmin($user, $tenant);
    }

    private function isOwnerOrAdmin(User $user, Tenant $tenant): bool
    {
        if (!$user->isCurrentTenant($tenant)) {
            return false;
        }

        return TenantScopedRoles::userHasAnyRole($user, $tenant->id, [RoleName::Owner->value, RoleName::Admin->value]);
    }
}
