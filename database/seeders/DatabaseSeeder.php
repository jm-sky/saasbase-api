<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSettings;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Products\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Domain\Tenant\Models\Tenant;
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
            'first_name' => config('users.default_user.first_name'),
            'last_name'  => config('users.default_user.last_name'),
            'email'      => config('users.default_user.email'),
            'password'   => Hash::make(config('users.default_user.password')),
            'is_admin'   => config('users.default_user.is_admin'),
        ]);

        $user->settings()->create(UserSettings::defaults());
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
