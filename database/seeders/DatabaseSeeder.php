<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Models\Tenant;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'first_name' => env('DEFAULT_USER_FIRST_NAME', 'Test'),
            'last_name'  => env('DEFAULT_USER_LAST_NAME', 'User'),
            'email'      => env('DEFAULT_USER_EMAIL', 'test@example.com'),
            'password'   => Hash::make(env('DEFAULT_USER_PASSWORD', 'Secret123!')),
            'is_admin'   => true,
        ]);

        $user->tenants()->attach($tenant, ['role' => 'admin']);

        $this->call([
            CountrySeeder::class,
            VatRateSeeder::class,
            SkillCategorySeeder::class,
            SkillSeeder::class,
            ProjectRoleSeeder::class,
            MeasurementUnitSeeder::class,
        ]);

        Contractor::factory(5)->create([
            'tenant_id' => $tenant->id,
        ]);

        Product::factory(5)->create([
            'tenant_id' => $tenant->id,
        ]);
    }
}
