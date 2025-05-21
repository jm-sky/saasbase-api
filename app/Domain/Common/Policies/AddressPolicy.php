<?php

namespace App\Domain\Common\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Address;
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
     * Determine whether the user can create addresses.
     */
    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant);
    }

    /**
     * Determine whether the user can update the address.
     */
    public function update(User $user, Address $address, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant) && $address->tenant_id === $tenant->id;
    }

    /**
     * Determine whether the user can delete the address.
     */
    public function delete(User $user, Address $address, Tenant $tenant): bool
    {
        return $user->isCurrentTenant($tenant) && $address->tenant_id === $tenant->id;
    }
}
