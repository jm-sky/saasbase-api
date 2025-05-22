<?php

namespace Database\Seeders;

use App\Domain\Auth\Enums\UserStatus;
use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Address;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Enums\UserTenantRole;
use App\Domain\Tenant\Listeners\CreateTenantForNewUser;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CustomTenantUserSeeder extends Seeder
{
    protected static string $seedFile = '_custom.json';

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
        foreach ($tenants as $tenantData) {
            $tenantData   = collect($tenantData)->except(['addresses', 'bankAccounts'])->toArray();
            $addresses    = collect(Arr::get($tenantData, 'addresses', []))->map(fn (array $address) => Address::create($address))->toArray();
            $bankAccounts = collect(Arr::get($tenantData, 'bankAccounts', []))->map(fn (array $bankAccount) => BankAccount::create($bankAccount))->toArray();

            $tenant = Tenant::create($tenantData);
            $tenant->addresses()->createMany($addresses);
            $tenant->bankAccounts()->createMany($bankAccounts);

            $this->tenants[$tenant->id] = $tenant;
        }
    }

    protected function createUsers(array $users): void
    {
        CreateTenantForNewUser::$BYPASSED = true;

        foreach ($users as $userData) {
            $userPayload             = collect($userData)->except(['tenant', 'meta'])->toArray();
            $user                    = User::create($userPayload);
            $user->status            = UserStatus::ACTIVE;
            $user->email_verified_at = now();
            $user->save();

            $this->createUserTenant($user, Arr::get($userData, 'tenant'));
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

            $signedUrl = $user->getMediaSignedUrl('profile');

            $user->update(['avatar_url' => $signedUrl]);
        } catch (\Exception $e) {
            $this->command->error("Error creating user avatar: {$e->getMessage()}");

            return;
        }
    }

    protected function createContractors(array $contractors): void
    {
        foreach ($contractors as $contractor) {
            $tenantId       = Arr::get($contractor, 'tenant_id');
            $contractorData = collect($contractor)->except(['addresses', 'bankAccounts'])->toArray();
            $addresses      = collect(Arr::get($contractorData, 'addresses', []))->map(fn (array $address) => Address::create($address))->toArray();
            $bankAccounts   = collect(Arr::get($contractorData, 'bankAccounts', []))->map(fn (array $bankAccount) => BankAccount::create($bankAccount))->toArray();

            Tenant::bypassTenant($tenantId, function () use ($contractorData, $addresses, $bankAccounts) {
                $contractor = Contractor::create($contractorData);
                $contractor->addresses()->createMany($addresses);
                $contractor->bankAccounts()->createMany($bankAccounts);
            });
        }
    }
}
