<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Projects\Models\ProjectRole;
use App\Domain\Projects\Models\ProjectStatus;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Tenant\Actions\InitializeTenantDefaults;
use App\Domain\Tenant\Enums\UserTenantRole;
use App\Domain\Tenant\Listeners\CreateTenantForNewUser;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
        $this->createProjects();
    }

    public static function shouldRun(): bool
    {
        return file_exists(static::getSeedFilePath());
    }

    public static function getSeedFilePath(): string
    {
        return database_path('data/' . static::$seedFile);
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
            $addresses    = collect(Arr::get($tenantInput, 'relations.addresses', []))->map(fn ($address) => [...$address, 'tenant_id' => $tenantId])->toArray();
            $bankAccounts = collect(Arr::get($tenantInput, 'relations.bankAccounts', []))->map(fn ($bankAccount) => [...$bankAccount, 'tenant_id' => $tenantId])->toArray();

            $tenant = Tenant::create($tenantData);

            Tenant::bypassTenant($tenant->id, function () use ($tenant, $addresses, $bankAccounts) {
                $tenant->addresses()->createMany($addresses);
                $tenant->bankAccounts()->createMany($bankAccounts);
                $tenant->subscription()->create([
                    'id'                     => (string) Str::ulid(),
                    'subscription_plan_id'   => SubscriptionPlan::where('name', 'Free')->firstOrFail()->id,
                    'stripe_subscription_id' => null,
                    'status'                 => SubscriptionStatus::ACTIVE,
                    'current_period_start'   => now(),
                    'current_period_end'     => now()->addYear(),
                    'cancel_at_period_end'   => false,
                ]);

                $initializer = new InitializeTenantDefaults();
                $initializer->createRootOrganizationUnit($tenant);
                $initializer->seedDefaultMeasurementUnits($tenant);
                $initializer->seedDefaultProjectStatuses($tenant);
            });

            $this->createTenantLogo($tenant, Arr::get($tenantInput, 'meta.logoUrl'));

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

            $user->profile()->create(Arr::get($userData, 'relations.profile', []));
            $user->settings()->create(Arr::get($userData, 'relations.settings', []));
            $user->preferences()->create(Arr::get($userData, 'relations.preferences', []));

            $this->createUserTenant($user, Arr::get($userData, 'relations.tenant'));
            $this->createUserSkills($user, Arr::get($userData, 'relations.skills'));
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

        $tenant = $this->tenants[$tenantId] ?? null;

        if (!$tenant) {
            return;
        }

        $tenant->users()->attach($user, ['role' => $role]);

        if ($isOwner) {
            $tenant->owner_id = $user->id;
            $tenant->save();
        }
    }

    protected function createUserSkills(User $user, ?array $skills = null): void
    {
        if (!$skills) {
            return;
        }

        collect($skills)->each(function (array $skill) use ($user) {
            UserSkill::create([
                'user_id'     => $user->id,
                'skill_id'    => Skill::firstOrCreate(['name' => $skill['name']])->id,
                'level'       => $skill['level'] ?? 3,
                'acquired_at' => $skill['acquired_at'] ?? now(),
            ]);
        });
    }

    protected function createTenantLogo(Tenant $tenant, ?string $logoUrl = null): void
    {
        if (!$logoUrl) {
            return;
        }

        try {
            $stream = $this->getCachedImageStream($logoUrl);

            if (!$stream) {
                return;
            }

            $tenant->clearMediaCollection('logo');

            $tenant->addMediaFromStream($stream)
                ->usingFileName('logo.png')
                ->toMediaCollection('logo')
            ;
        } catch (\Exception $e) {
            $this->command->error("Error creating tenant logo: {$e->getMessage()}");
        }
    }

    protected function createUserAvatar(User $user, ?string $avatarUrl = null): void
    {
        if (!$avatarUrl) {
            return;
        }

        try {
            $stream = $this->getCachedImageStream($avatarUrl);

            if (!$stream) {
                return;
            }

            $user->clearMediaCollection('profile');

            $user->addMediaFromStream($stream)
                ->usingFileName('profile.png')
                ->toMediaCollection('profile')
            ;
        } catch (\Exception $e) {
            $this->command->error("Error creating user avatar: {$e->getMessage()}");
        }
    }

    protected function createContractors(array $contractors): void
    {
        foreach ($contractors as $contractorData) {
            $tenantId     = Arr::get($contractorData, 'tenant_id');
            $addresses    = collect(Arr::get($contractorData, 'relations.addresses', []))->map(fn ($address) => [...$address, 'tenant_id' => $tenantId])->toArray();
            $bankAccounts = collect(Arr::get($contractorData, 'relations.bankAccounts', []))->map(fn ($bankAccount) => [...$bankAccount, 'tenant_id' => $tenantId])->toArray();

            Tenant::bypassTenant($tenantId, function () use ($contractorData, $addresses, $bankAccounts) {
                $contractor = Contractor::create(collect($contractorData)->except(['relations', 'meta'])->toArray());
                $contractor->addresses()->createMany($addresses);
                $contractor->bankAccounts()->createMany($bankAccounts);
                $this->createContractorLogo($contractor, Arr::get($contractorData, 'meta.logoUrl'));
            });
        }
    }

    protected function createContractorLogo(Contractor $contractor, ?string $logoUrl = null): void
    {
        if (!$logoUrl) {
            return;
        }

        try {
            $stream = $this->getCachedImageStream($logoUrl);

            if (!$stream) {
                return;
            }

            $contractor->clearMediaCollection('logo');

            $contractor->addMediaFromStream($stream)
                ->usingFileName('logo.png')
                ->toMediaCollection('logo')
            ;
        } catch (\Exception $e) {
            $this->command->error("Error creating contractor logo: {$e->getMessage()}");
        }
    }

    private function getCachedImageStream(string $url): mixed
    {
        $cacheDir = storage_path('app/seeder_cache');

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
        $filename  = md5($url) . '.' . $extension;
        $filepath  = $cacheDir . '/' . $filename;

        if (!file_exists($filepath)) {
            try {
                $contents = file_get_contents($url);

                if (!$contents) {
                    return null;
                }

                file_put_contents($filepath, $contents);
            } catch (\Exception $e) {
                $this->command->error("Failed to download image from URL: {$url} ({$e->getMessage()})");

                return null;
            }
        }

        return fopen($filepath, 'r');
    }

    protected function createProjects(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            Tenant::bypassTenant($tenant->id, function () use ($tenant) {
                $status = ProjectStatus::withoutTenant()->where('tenant_id', $tenant->id)->where('is_default', true)->first();

                $project = $tenant->projects()->create([
                    'name'        => 'Onboarding',
                    'status_id'   => $status->id,
                    'description' => 'Tenants onboarding project for new users',
                    'start_date'  => now(),
                    'owner_id'    => $tenant->owner_id,
                ]);

                $users         = $tenant->users;
                $developerRole = ProjectRole::where('name', 'Developer')->first();

                foreach ($users as $user) {
                    $user->projects()->attach($project->id, ['project_role_id' => $developerRole->id]);
                }
            });
        }
    }
}
