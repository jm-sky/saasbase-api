<?php

namespace Database\Seeders;

use App\Domain\Common\Models\Tag;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            CountrySeeder::class,
            VatRateSeeder::class,
            SkillCategorySeeder::class,
            SkillSeeder::class,
            ProjectRoleSeeder::class,
            DefaultMeasurementUnitSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        Tenant::all()->each(function (Tenant $tenant) {
            collect(['VIP', 'Test'])->each(fn (string $tag) => Tag::create([
                'tenant_id' => $tenant->id,
                'name'      => $tag,
                'slug'      => Str::slug($tag),
            ]));
        });

        if (CustomTenantUserSeeder::shouldRun()) {
            $this->call(CustomTenantUserSeeder::class);
        } else {
            $this->call(DefaultTenantSeeder::class);
        }
    }
}
