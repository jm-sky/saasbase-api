<?php

namespace App\Domain\Tenant\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\TenantIntegration;
use Illuminate\Auth\Access\HandlesAuthorization;

class TenantIntegrationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->getTenantId() !== null;
    }

    public function view(User $user, TenantIntegration $integration): bool
    {
        return $this->isOwnerOrAdmin($user, $integration->tenant_id);
    }

    public function create(User $user): bool
    {
        $tenantId = $user->getTenantId();

        return $tenantId !== null && $this->isOwnerOrAdmin($user, $tenantId);
    }

    public function update(User $user, TenantIntegration $integration): bool
    {
        return $this->isOwnerOrAdmin($user, $integration->tenant_id);
    }

    public function delete(User $user, TenantIntegration $integration): bool
    {
        return $this->isOwnerOrAdmin($user, $integration->tenant_id);
    }

    private function isOwnerOrAdmin(User $user, string $tenantId): bool
    {
        if ($user->getTenantId() !== $tenantId) {
            return false;
        }

        return TenantScopedRoles::userHasAnyRole($user, $tenantId, [RoleName::Owner->value, RoleName::Admin->value]);
    }
}
