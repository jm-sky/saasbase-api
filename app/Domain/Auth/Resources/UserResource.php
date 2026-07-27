<?php

namespace App\Domain\Auth\Resources;

use App\Domain\Auth\Models\User;
use App\Domain\Rights\Support\TenantScopedRoles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tenantId = $this->getTenantId();

        // Spatie getRoleNames()/getAllPermissions() ignore pivot tenant_id
        // unless PermissionRegistrar::setPermissionsTeamId() was called —
        // which nothing in the request lifecycle does. Policies already use
        // TenantScopedRoles; /me must match so the frontend can gate UI.
        $roles = $tenantId
            ? TenantScopedRoles::roleNamesFor($this->resource, $tenantId)
            : [];
        $permissions = $tenantId
            ? TenantScopedRoles::permissionNamesFor($this->resource, $tenantId)
            : [];

        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'avatarUrl' => $this->getMediaSignedUrl('profile'),
            'description' => $this->profile?->bio,
            'birthDate' => $this->profile?->birth_date,
            'phone' => $this->phone,
            'isAdmin' => $this->is_admin,
            'isEmailVerified' => $this->isEmailVerified(),
            'isTwoFactorEnabled' => $this->isTwoFactorEnabled(),
            'roles' => $roles,
            'permissions' => $permissions,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
