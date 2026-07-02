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
