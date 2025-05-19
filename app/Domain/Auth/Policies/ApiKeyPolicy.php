<?php

namespace App\Domain\Auth\Policies;

use App\Domain\Auth\Models\ApiKey;
use App\Domain\Auth\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiKeyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->user_id
               && $user->getCurrentTenantId() === $apiKey->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->user_id
               && $user->getCurrentTenantId() === $apiKey->tenant_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->user_id
               && $user->getCurrentTenantId() === $apiKey->tenant_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ApiKey $apiKey): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ApiKey $apiKey): bool
    {
        return false;
    }
}
