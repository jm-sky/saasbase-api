<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSettings;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Actions\InitializeTenantDefaults;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public const BOT_USER_ID = 'd99468d6-2153-5493-95dc-7cf06043f471';

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

        // Event listener will create the tenant for the user
        $user->tenants()->attach($tenant, ['role' => 'admin']);

        $this->createBotUser($tenant);

        $this->call([
            CountrySeeder::class,
            VatRateSeeder::class,
            SkillCategorySeeder::class,
            SkillSeeder::class,
            ProjectRoleSeeder::class,
            DefaultMeasurementUnitSeeder::class,
        ]);

        (new InitializeTenantDefaults())->execute($tenant, $user);

        Contractor::factory(5)->create([
            'tenant_id' => $tenant->id,
        ])->each(function ($contractor) use ($tenant) {
            // Dodaj 2 adresy
            \Database\Factories\AddressFactory::new()->count(2)->create([
                'tenant_id'        => $tenant->id,
                'addressable_id'   => $contractor->id,
                'addressable_type' => \App\Domain\Contractors\Models\Contractor::class,
            ]);
            // Dodaj 1-2 tagi
            $tags = collect(['VIP', 'Partner', 'Nowy', 'Kluczowy', 'Testowy'])->random(rand(1, 2))->all();
            foreach ($tags as $tag) {
                $contractor->addTag($tag, $tenant->id);
            }
        });

        Product::factory(5)->create([
            'tenant_id' => $tenant->id,
        ]);
    }

    protected function createBotUser(Tenant $tenant): void
    {
        $botUser = User::factory()->create([
            'id'         => self::BOT_USER_ID,
            'first_name' => 'Botto',
            'last_name'  => 'Bot',
            'email'      => 'bot@example.com',
            'password'   => Hash::make(config('users.default_user.password')),
        ]);

        $botUser->tenants()->attach($tenant, ['role' => 'user']);
    }
}
