<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Tenant;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
use App\Enums\OrgUnitRole;

class TenantStructureSeeder extends Seeder
{
    public function run(): void
    {
        // Użytkownik (CEO)
        $user = User::firstOrCreate(
            ['email' => 'ceo@example.com'],
            [
                'name' => 'CEO',
                'password' => bcrypt('password'),
            ]
        );

        // Tenant
        $tenant = $user->tenants()->firstOrCreate(
            ['name' => 'Demo Company'],
            ['id' => (string) Str::uuid()]
        );

        // Zapewniamy istnienie wpisu w tabeli user_tenant
        $user->tenants()->syncWithoutDetaching([$tenant->id]);

        // Root OrganizationUnit (nazwa jak tenant, short_name z slug'a)
        $rootUnit = OrganizationUnit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'parent_id' => null],
            [
                'id' => (string) Str::uuid(),
                'name' => $tenant->name,
                'short_name' => Str::slug($tenant->name),
            ]
        );

        // Powiązanie użytkownika z jednostką organizacyjną jako CEO
        OrgUnitUser::firstOrCreate(
            [
                'organization_unit_id' => $rootUnit->id,
                'user_id' => $user->id,
            ],
            [
                'id' => (string) Str::uuid(),
                'role' => OrgUnitRole::CEO,
            ]
        );

        $this->command->info('Tenant structure seeded successfully.');
    }
}
