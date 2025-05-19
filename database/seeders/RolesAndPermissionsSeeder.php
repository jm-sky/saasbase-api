<?php

namespace Database\Seeders;

use App\Domain\Rights\Models\Permission;
use App\Domain\Rights\Models\Role;
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
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name'       => $permission,
                'guard_name' => 'api',
                'tenant_id'  => null, // Global permissions
            ]);
        }

        // Create roles and assign permissions
        $roles = [
            'Admin'            => $permissions,
            'Owner'            => $permissions,
            'FinancialManager' => [
                'contractor.view',
                'contractor.manage',
                'product.view',
                'product.manage',
            ],
            'ProjectManager' => [
                'project.view',
                'project.manage',
                'task.view',
                'task.manage',
            ],
            'ProjectMember' => [
                'project.view',
                'task.view',
                'task.manage',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create([
                'name'       => $roleName,
                'guard_name' => 'api',
                'tenant_id'  => null, // Global roles
            ]);

            $role->syncPermissions($rolePermissions);
        }
    }
}
