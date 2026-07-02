<?php

namespace App\Domain\Rights\Support;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * The app never calls PermissionRegistrar::setPermissionsTeamId() during the
 * request lifecycle (only database/seeders/RolesAndPermissionsSeeder.php does),
 * so Spatie's team-scoped assignRole()/hasRole() operate against a stale/empty
 * team context outside of seeding. These helpers scope writes explicitly around
 * the Spatie call, and read role membership directly from the pivot table, so
 * they behave correctly regardless of ambient registrar state.
 */
class TenantScopedRoles
{
    public static function assign(User $user, string $roleName, string $tenantId): void
    {
        self::withTeamId($tenantId, static fn () => $user->assignRole($roleName));
    }

    public static function remove(User $user, string $roleName, string $tenantId): void
    {
        self::withTeamId($tenantId, static fn () => $user->removeRole($roleName));
    }

    public static function userHasAnyRole(User $user, string $tenantId, array $roleNames): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', $user->getMorphClass())
            ->where('model_has_roles.tenant_id', $tenantId)
            ->whereIn('roles.name', $roleNames)
            ->exists()
        ;
    }

    /**
     * IDs of users holding $permissionName within $tenantId, either via a
     * direct permission grant or via a tenant-scoped role that carries it.
     * Same rationale as the rest of this class: model_has_permissions/
     * model_has_roles both have a tenant_id column, but Spatie's own
     * whereHas('permissions'|'roles', ...) helpers ignore it unless
     * setPermissionsTeamId() was called first — which nothing in the
     * request lifecycle does.
     */
    public static function userIdsWithPermission(string $permissionName, string $tenantId, string $modelType = User::class): array
    {
        $direct = DB::table('model_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('permissions.name', $permissionName)
            ->where('model_has_permissions.tenant_id', $tenantId)
            ->where('model_has_permissions.model_type', $modelType)
            ->pluck('model_has_permissions.model_id')
        ;

        $viaRole = DB::table('model_has_roles')
            ->join('role_has_permissions', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('permissions.name', $permissionName)
            ->where('model_has_roles.tenant_id', $tenantId)
            ->where('model_has_roles.model_type', $modelType)
            ->pluck('model_has_roles.model_id')
        ;

        return $direct->merge($viaRole)->unique()->values()->all();
    }

    private static function withTeamId(string $tenantId, \Closure $callback): mixed
    {
        /** @var PermissionRegistrar $registrar */
        $registrar = app(PermissionRegistrar::class);
        $previous  = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($tenantId);

        try {
            return $callback();
        } finally {
            $registrar->setPermissionsTeamId($previous);
        }
    }
}
