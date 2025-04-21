<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Contractor\Models\Contractor;
use App\Domain\Product\Models\Product;
use App\Domain\Tenant\Models\Tenant;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default tenant
        $tenant = Tenant::factory()->create([
            'name' => 'Default Tenant',
        ]);

        // Create default user
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name'  => 'User',
            'email'      => 'test@example.com',
        ]);

        // Attach user to tenant
        UserTenant::create([
            'user_id'   => $user->id,
            'tenant_id' => $tenant->id,
            'role'      => 'admin',
        ]);

        $this->call([
            CountrySeeder::class,
            VatRateSeeder::class,
            SkillCategorySeeder::class,
            SkillSeeder::class,
            ProjectRoleSeeder::class,
            MeasurementUnitSeeder::class,
        ]);

        Contractor::factory(5)->create([
            'tenant_id' => Tenant::first()->id,
        ]);

        Product::factory(5)->create([
            'tenant_id' => Tenant::first()->id,
        ]);
    }
}
