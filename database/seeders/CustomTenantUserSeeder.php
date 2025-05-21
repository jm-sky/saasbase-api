<?php

namespace Database\Seeders;

use App\Domain\Auth\Enums\UserStatus;
use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Enums\UserTenantRole;
use App\Domain\Tenant\Listeners\CreateTenantForNewUser;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CustomTenantUserSeeder extends Seeder
{
    protected static string $seedFile = '_tenantsUsersSeed.json';

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

        $this->createTenants($this->data['tenants']);
        $this->createUsers($this->data['users']);
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
            $tenant = Tenant::create($tenantData);

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
}
