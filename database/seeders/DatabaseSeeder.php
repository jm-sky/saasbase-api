<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (CustomTenantUserSeeder::shouldRun()) {
            $this->call(CustomTenantUserSeeder::class);
        } else {
            $this->call(DefaultTenantSeeder::class);
        }

        $this->call([
            CountrySeeder::class,
            VatRateSeeder::class,
            SkillCategorySeeder::class,
            SkillSeeder::class,
            ProjectRoleSeeder::class,
            DefaultMeasurementUnitSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }
}
