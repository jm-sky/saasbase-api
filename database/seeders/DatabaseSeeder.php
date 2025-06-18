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
        $this->call([
            FeatureSeeder::class,
            PlanSeeder::class,
            PlanFeatureSeeder::class,
            CurrencySeeder::class,
            CountrySeeder::class,
            VatRateSeeder::class,
            PaymentMethodSeeder::class,
            SkillCategorySeeder::class,
            SkillSeeder::class,
            ProjectRoleSeeder::class,
            DefaultMeasurementUnitSeeder::class,
            RolesAndPermissionsSeeder::class,
            NumberingTemplateSeeder::class,
            DefaultStatusesSeeder::class,
        ]);

        if (CustomTenantUserSeeder::shouldRun()) {
            $this->call(CustomTenantUserSeeder::class);
        } else {
            $this->call(DefaultTenantSeeder::class);
        }
    }
}
