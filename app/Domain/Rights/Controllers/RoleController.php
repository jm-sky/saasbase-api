<?php

namespace App\Domain\Rights\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Models\Role;
use App\Domain\Rights\Requests\StoreRoleRequest;
use App\Domain\Rights\Requests\UpdateRoleRequest;
use App\Domain\Rights\Resources\RoleResource;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();

        $roles = Role::with('permissions')
            ->where(function ($query) use ($user) {
                $query->whereNull('tenant_id')->orWhere('tenant_id', $user->getTenantId());
            })
            ->get();

        return RoleResource::collection($roles);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        if (! $this->canManageRoles($user)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'api',
            'tenant_id' => $user->getTenantId(),
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json(new RoleResource($role), Response::HTTP_CREATED);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        // Prevent modifying global roles
        if ($role->tenant_id === null) {
            return response()->json(['message' => 'Cannot modify global roles'], Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = Auth::user();

        // Ensure the role belongs to the current tenant
        if ($role->tenant_id !== $user->getTenantId()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        if (! $this->canManageRoles($user)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json(new RoleResource($role));
    }

    public function destroy(Role $role): JsonResponse
    {
        // Prevent deleting global roles
        if ($role->tenant_id === null) {
            return response()->json(['message' => 'Cannot delete global roles'], Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = Auth::user();

        // Ensure the role belongs to the current tenant
        if ($role->tenant_id !== $user->getTenantId()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        if (! $this->canManageRoles($user)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $role->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function canManageRoles(User $user): bool
    {
        $tenantId = $user->getTenantId();

        if (! $tenantId) {
            return false;
        }

        return TenantScopedRoles::userHasAnyRole($user, $tenantId, [RoleName::Owner->value, RoleName::Admin->value]);
    }
}
