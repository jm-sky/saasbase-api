<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSettings;
use App\Domain\Auth\Notifications\PasswordChangedNotification;
use App\Domain\Auth\Notifications\WelcomeNotification;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Actions\InitializeTenantDefaults;
use App\Domain\Tenant\Enums\UserTenantRole;
use App\Domain\Tenant\Listeners\CreateTenantForNewUser;
use App\Domain\Tenant\Models\Tenant;
use Database\Factories\AddressFactory;
use Database\Factories\BankAccountFactory;
use Database\Factories\CommentFactory;
use Database\Factories\ContractorContactPersonFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DefaultTenantSeeder extends Seeder
{
    protected string $botUserId = '01JXMGRDQVE4FWTZNE1WR9K8G1';

    public function run(): void
    {
        CreateTenantForNewUser::$BYPASSED = true;

        $this->botUserId = config('seeding.bot_user_id', $this->botUserId);

        $user   = $this->createDefaultUser();
        $tenant = $this->createTenant($user);

        $this->createBotUser($tenant);
        $this->createMockedData($tenant);
    }

    protected function createDefaultUser(): User
    {
        $user = User::factory()->create([
            'first_name' => config('users.default_user.first_name'),
            'last_name'  => config('users.default_user.last_name'),
            'email'      => config('users.default_user.email'),
            'password'   => Hash::make(config('users.default_user.password')),
            'is_admin'   => config('users.default_user.is_admin'),
        ]);

        $user->notify(new WelcomeNotification($user));
        $user->notify(new PasswordChangedNotification($user));
        $user->settings()->create(UserSettings::defaults());

        return $user;
    }

    protected function createTenant(User $user): Tenant
    {
        $tenant = Tenant::factory()->create(CreateTenantForNewUser::prepareTenantData($user));

        $user->tenants()->attach($tenant, ['role' => UserTenantRole::Admin->value]);

        (new InitializeTenantDefaults())->execute($tenant, $user);

        return $tenant;
    }

    protected function createMockedData(Tenant $tenant)
    {
        Tenant::bypassTenant($tenant->id, function () use ($tenant) {
            Contractor::factory(15)->create([
                'tenant_id' => $tenant->id,
            ])->each(function ($contractor) use ($tenant) {
                AddressFactory::new()->count(3)->create([
                    'tenant_id'        => $tenant->id,
                    'addressable_id'   => $contractor->id,
                    'addressable_type' => Contractor::class,
                ]);

                BankAccountFactory::new()->count(3)->create([
                    'tenant_id'     => $tenant->id,
                    'bankable_id'   => $contractor->id,
                    'bankable_type' => Contractor::class,
                ]);

                ContractorContactPersonFactory::new()->count(3)->create([
                    'tenant_id'     => $tenant->id,
                    'contractor_id' => $contractor->id,
                ]);

                $tags = collect(['VIP', 'Partner', 'Nowy', 'Kluczowy', 'Testowy'])->random(rand(1, 2))->all();

                foreach ($tags as $tag) {
                    $contractor->addTag($tag, $tenant->id);
                }

                // Add some random comments for each contractor
                CommentFactory::new()->count(rand(1, 3))->create([
                    'tenant_id'        => $tenant->id,
                    'user_id'          => $this->botUserId,
                    'commentable_id'   => $contractor->id,
                    'commentable_type' => Contractor::class,
                ]);

                Product::factory(15)->create([
                    'tenant_id' => $tenant->id,
                ]);
            });
        });
    }

    protected function createBotUser(Tenant $tenant): void
    {
        $domain = Str::after(config('app.url'), '://');

        $botUser = User::factory()->create([
            'id'         => $this->botUserId,
            'first_name' => 'Botto',
            'last_name'  => 'Bot',
            'email'      => "botto.bot@{$domain}",
            'password'   => Hash::make(config('users.default_user.password')),
        ]);

        $botUser->tenants()->attach($tenant, ['role' => 'user']);
    }
}
