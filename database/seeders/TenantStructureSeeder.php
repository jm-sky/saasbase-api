<?php

namespace Database\Seeders;

use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
use App\Enums\OrgUnitRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantStructureSeeder extends Seeder
{
    public function run(): void
    {
        // Użytkownik (CEO)
        $user = User::firstOrCreate(
            ['email' => 'ceo@example.com'],
            [
                'name'     => 'CEO',
                'password' => bcrypt('password'),
            ]
        );

        // Tenant
        $tenant = $user->tenants()->firstOrCreate(
            ['name' => 'Demo Company'],
            ['id' => (string) Str::ulid()]
        );

        // Zapewniamy istnienie wpisu w tabeli user_tenant
        $user->tenants()->syncWithoutDetaching([$tenant->id]);

        // Root OrganizationUnit (nazwa jak tenant, short_name z slug'a)
        $rootUnit = OrganizationUnit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'parent_id' => null],
            [
                'id'         => (string) Str::ulid(),
                'name'       => $tenant->name,
                'short_name' => Str::slug($tenant->name),
            ]
        );

        // Powiązanie użytkownika z jednostką organizacyjną jako CEO
        OrgUnitUser::firstOrCreate(
            [
                'organization_unit_id' => $rootUnit->id,
                'user_id'              => $user->id,
            ],
            [
                'id'   => (string) Str::ulid(),
                'role' => OrgUnitRole::CEO,
            ]
        );

        $this->command->info('Tenant structure seeded successfully.');
    }
}
