<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Enums\UserTenantRole;
use App\Domain\Tenant\Listeners\CreateTenantForNewUser;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CustomTenantUserSeeder extends Seeder
{
    protected static string $seedFile = '_custom.json';

    /**
     * @var array{
     *     tenants?: array<array{
     *         id: string,
     *         name: string,
     *         relations?: array{
     *             addresses?: array<array{
     *                 street: string,
     *                 city: string,
     *                 country: string,
     *                 postal_code: string
     *             }>,
     *             bankAccounts?: array<array{
     *                 iban: string,
     *                 bank_name: string
     *             }>
     *         },
     *         meta?: array{
     *             logoUrl?: string
     *         }
     *     }>,
     *     users?: array<array{
     *         first_name: string,
     *         last_name: string,
     *         email: string,
     *         password: string,
     *         phone?: string,
     *         relations?: array{
     *             tenant?: array{
     *                 id: string,
     *                 role?: string,
     *                 isOwner?: bool
     *             }
     *         },
     *         meta?: array{
     *             avatarUrl?: string
     *         }
     *     }>,
     *     contractors?: array<array{
     *         tenant_id: string,
     *         name: string,
     *         relations?: array{
     *             addresses?: array<array{
     *                 street: string,
     *                 city: string,
     *                 country: string,
     *                 postal_code: string
     *             }>,
     *             bankAccounts?: array<array{
     *                 iban: string,
     *                 bank_name: string
     *             }>
     *         },
     *         meta?: array{
     *             logoUrl?: string
     *         }
     *     }>
     * }
     */
    protected array $data = [];

    protected array $tenants = [];

    protected array $users = [];

    public function run(): void
    {
        if (!static::shouldRun()) {
            return;
        }

        $this->loadData();

        if (empty($this->data)) {
            return;
        }

        $this->createTenants(Arr::get($this->data, 'tenants', []));
        $this->createUsers(Arr::get($this->data, 'users', []));
        $this->createContractors(Arr::get($this->data, 'contractors', []));
    }

    public static function shouldRun(): bool
    {
        return file_exists(static::getSeedFilePath());
    }

    public static function getSeedFilePath(): string
    {
        $file = static::$seedFile;

        return database_path("data/{$file}");
    }

    protected function loadData(): void
    {
        $this->data = json_decode(file_get_contents(static::getSeedFilePath()), true);
    }

    protected function createTenants(array $tenants): void
    {
        foreach ($tenants as $tenantInput) {
            $tenantId     = Arr::get($tenantInput, 'id');
            $tenantData   = collect($tenantInput)->except(['relations', 'meta'])->toArray();
            $addresses    = collect(Arr::get($tenantInput, 'relations.addresses', []))->map(fn (array $address) => [...$address, 'tenant_id' => $tenantId])->toArray();
            $bankAccounts = collect(Arr::get($tenantInput, 'relations.bankAccounts', []))->map(fn (array $bankAccount) => [...$bankAccount, 'tenant_id' => $tenantId])->toArray();

            $tenant = Tenant::create($tenantData);

            Tenant::bypassTenant($tenant->id, function () use ($tenant, $addresses, $bankAccounts) {
                $tenant->addresses()->createMany($addresses);
                $tenant->bankAccounts()->createMany($bankAccounts);
            });

            $this->createTenantLogo($tenant, Arr::get($tenantData, 'meta.logoUrl'));

            $this->tenants[$tenant->id] = $tenant;
        }
    }

    protected function createUsers(array $users): void
    {
        CreateTenantForNewUser::$BYPASSED = true;

        foreach ($users as $userData) {
            $userPayload             = collect($userData)->except(['relations', 'meta'])->toArray();
            $user                    = User::create($userPayload);
            $user->is_active         = true;
            $user->email_verified_at = now();
            $user->save();

            $this->createUserTenant($user, Arr::get($userData, 'relations.tenant'));
            $this->createUserAvatar($user, Arr::get($userData, 'meta.avatarUrl'));

            $this->users[$user->id] = $user;
        }
    }

    protected function createUserTenant(User $user, ?array $tenantData = null): void
    {
        if (!$tenantData) {
            return;
        }

        $tenantId = Arr::get($tenantData, 'id');
        $role     = Arr::get($tenantData, 'role') ?? UserTenantRole::User->value;
        $isOwner  = Arr::get($tenantData, 'isOwner') ?? false;

        /** @var ?Tenant $tenant */
        $tenant = Arr::get($this->tenants, $tenantId);

        if (!$tenant) {
            return;
        }

        $tenant->users()->attach($user, ['role' => $role]);

        if ($isOwner) {
            $tenant->owner_id = $user->id;
            $tenant->save();
        }
    }

    protected function createTenantLogo(Tenant $tenant, ?string $logoUrl = null): void
    {
        if (!$logoUrl) {
            return;
        }

        try {
            $stream = fopen($logoUrl, 'r');
            $tenant->clearMediaCollection('logo');

            $tenant->addMediaFromStream($stream)
                ->usingFileName('logo.png')
                ->toMediaCollection('logo')
            ;
        } catch (\Exception $e) {
            $this->command->error("Error creating tenant logo: {$e->getMessage()}");

            return;
        }
    }

    protected function createUserAvatar(User $user, ?string $avatarUrl = null): void
    {
        if (!$avatarUrl) {
            return;
        }

        try {
            $stream = fopen($avatarUrl, 'r');
            $user->clearMediaCollection('profile');

            $user->addMediaFromStream($stream)
                ->usingFileName('profile.png')
                ->toMediaCollection('profile')
            ;
        } catch (\Exception $e) {
            $this->command->error("Error creating user avatar: {$e->getMessage()}");

            return;
        }
    }

    protected function createContractors(array $contractors): void
    {
        foreach ($contractors as $contractor) {
            $tenantId       = Arr::get($contractor, 'tenant_id');
            $contractorData = collect($contractor)->except(['relations', 'meta'])->toArray();
            $addresses      = collect(Arr::get($contractor, 'relations.addresses', []))->map(fn (array $address) => [...$address, 'tenant_id' => $tenantId])->toArray();
            $bankAccounts   = collect(Arr::get($contractor, 'relations.bankAccounts', []))->map(fn (array $bankAccount) => [...$bankAccount, 'tenant_id' => $tenantId])->toArray();

            Tenant::bypassTenant($tenantId, function () use ($contractorData, $addresses, $bankAccounts) {
                $contractor = Contractor::create($contractorData);
                $contractor->addresses()->createMany($addresses);
                $contractor->bankAccounts()->createMany($bankAccounts);
                $this->createContractorLogo($contractor, Arr::get($contractor, 'meta.logoUrl'));
            });
        }
    }

    protected function createContractorLogo(Contractor $contractor, ?string $logoUrl = null): void
    {
        if (!$logoUrl) {
            return;
        }

        try {
            $stream = fopen($logoUrl, 'r');
            $contractor->clearMediaCollection('logo');

            $contractor->addMediaFromStream($stream)
                ->usingFileName('logo.png')
                ->toMediaCollection('logo')
            ;
        } catch (\Exception $e) {
            $this->command->error("Error creating contractor logo: {$e->getMessage()}");

            return;
        }
    }
}
