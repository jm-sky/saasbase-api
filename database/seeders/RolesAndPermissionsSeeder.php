<?php

namespace Database\Seeders;

use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Models\Permission;
use App\Domain\Rights\Models\Role;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Contractor permissions
            'contractor.view',
            'contractor.manage',

            // Project permissions
            'project.view',
            'project.manage',

            // Task permissions
            'task.view',
            'task.manage',

            // Product permissions
            'product.view',
            'product.manage',

            // Invoice template permissions
            'invoice_templates.manage',
        ];

        foreach ($permissions as $permission) {
            Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () use ($permission) {
                Permission::create([
                    'name'       => $permission,
                    'guard_name' => 'api',
                    'tenant_id'  => Tenant::GLOBAL_TENANT_ID,
                ]);
            });
        }

        // Create roles and assign permissions
        $roles = [
            RoleName::Admin->value            => $permissions,
            RoleName::Owner->value            => $permissions,
            RoleName::Manager->value          => $permissions,
            RoleName::FinancialManager->value => [
                'contractor.view',
                'contractor.manage',
                'product.view',
                'product.manage',
                'invoice_templates.manage',
            ],
            RoleName::ProjectManager->value => [
                'project.view',
                'project.manage',
                'task.view',
                'task.manage',
            ],
            RoleName::ProjectMember->value => [
                'project.view',
                'task.view',
                'task.manage',
            ],
            RoleName::User->value => [],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () use ($roleName, $rolePermissions) {
                $role = Role::create([
                    'name'       => $roleName,
                    'guard_name' => 'api',
                    'tenant_id'  => Tenant::GLOBAL_TENANT_ID,
                ]);

                $role->syncPermissions($rolePermissions);
            });
        }
    }
}
