<?php

namespace App\Domain\Tenant\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenantPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->where('tenants.id', $tenant->id)->exists();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $this->isOwnerOrAdmin($user, $tenant);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $this->isOwnerOrAdmin($user, $tenant);
    }

    private function isOwnerOrAdmin(User $user, Tenant $tenant): bool
    {
        if (! $user->tenants()->where('tenants.id', $tenant->id)->exists()) {
            return false;
        }

        return TenantScopedRoles::userHasAnyRole($user, $tenant->id, [RoleName::Owner->value, RoleName::Admin->value]);
    }
}
