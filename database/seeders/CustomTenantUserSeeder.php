<?php

namespace Database\Seeders;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Notifications\WelcomeNotification;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\VatRate;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Products\Enums\ProductType;
use App\Domain\Products\Models\Product;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Models\ProjectRole;
use App\Domain\Projects\Models\ProjectStatus;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Tenant\Actions\InitializeTenantDefaults;
use App\Domain\Tenant\Enums\DefaultPositionCategory;
use App\Domain\Tenant\Enums\UserTenantRole;
use App\Domain\Tenant\Listeners\CreateTenantForNewUser;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\PositionCategory;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Services\OrganizationPositionService;
use App\Helpers\Ulid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CustomTenantUserSeeder extends Seeder
{
    protected static string $seedFile = '_custom.json';

    protected array $data = [];

    /**
     * @var array<string, Tenant>
     */
    protected array $tenants = [];

    /**
     * @var array<string, User>
     */
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
        $this->createProductsForTenants();
        $this->createApiKeys();
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
                $initializer->createDefaultPositionCategories($tenant);
                $rootUnit    = $initializer->createOrganizationUnits($tenant, $tenant->owner);
                $initializer->seedDefaultMeasurementUnits($tenant);
                $initializer->seedDefaultProjectStatuses($tenant);
                $initializer->seedDefaultTags($tenant);

                $this->createOrganizationUnits($tenant, $rootUnit);
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

            /** @var User $user */
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

            if ($user->is_admin) {
                $user->notify((new WelcomeNotification($user))->viaDatabaseOnly());
            }

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

        $organizationPositionService = new OrganizationPositionService($tenant);

        if ($isOwner) {
            $tenant->owner_id = $user->id;
            $tenant->save();

            Tenant::bypassTenant($tenant->id, function () use ($tenant, $user, $organizationPositionService) {
                $directorPosition = $tenant->rootOrganizationUnit->positions()->where('name', DefaultPositionCategory::Director->value)->first();
                $organizationPositionService->assignUserToPosition($user, $tenant->rootOrganizationUnit, $directorPosition);
            });
        } else {
            Tenant::bypassTenant($tenant->id, function () use ($tenant, $user, $organizationPositionService) {
                $organizationPositionService->assignUserToPosition($user, $tenant->unassignedOrganizationUnit);
            });
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
        $tenants   = Tenant::all();
        $templates = [
            'onboarding' => [
                'name'        => 'Onboarding',
                'description' => 'Tenants onboarding project for new users. This project is used to onboard new users to the platform.',
            ],
            'saasbase-development' => [
                'name'        => 'SaasBase development',
                'description' => 'SaasBase development project to test our platform and add new features.',
            ],
            'project-management' => [
                'name'        => 'Project Management',
                'description' => 'Project management project for new tenants',
            ],
        ];

        foreach ($tenants as $tenant) {
            Tenant::bypassTenant($tenant->id, function () use ($tenant, $templates) {
                foreach ($templates as $template) {
                    $this->createProject($tenant, $template);
                }
            });
        }
    }

    protected function createProject(Tenant $tenant, array $template): void
    {
        /** @var ProjectStatus $status */
        $status = ProjectStatus::withoutTenant()->where('tenant_id', $tenant->id)->where('is_default', true)->first();

        /** @var Project $project */
        $project = $tenant->projects()->create([
            'name'        => $template['name'],
            'status_id'   => $status->id,
            'description' => $template['description'],
            'start_date'  => now(),
            'owner_id'    => $tenant->owner_id,
        ]);

        /** @var Collection<int, User> $users */
        $users = $tenant->users;

        /** @var ProjectRole $developerRole */
        $developerRole = ProjectRole::where('name', 'Developer')->first();

        foreach ($users as $user) {
            $user->projects()->attach($project->id, ['project_role_id' => $developerRole->id]);
        }
    }

    protected function createProductsForTenants(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            Tenant::bypassTenant($tenant->id, function () use ($tenant) {
                $this->createProducts($tenant);
                $this->createInvoices($tenant);
                $this->createExpenses($tenant);
            });
        }
    }

    protected function createProducts(Tenant $tenant): void
    {
        $unit    = MeasurementUnit::where('code', 'mh')->first();
        $vatRate = VatRate::where('name', '23%')->first();

        $products = [
            'product-1' => [
                'name'        => 'Usługi IT',
                'description' => 'Usługi IT dla klienta',
                'price_net'   => 200,
                'type'        => ProductType::SERVICE,
                'unit_id'     => $unit->id,
                'vat_rate_id' => $vatRate->id,
            ],
            'product-2' => [
                'name'        => 'Szkolenie z cyberbezpieczeństwa',
                'description' => 'Szkolenie z cyberbezpieczeństwa dla klienta',
                'price_net'   => 300,
                'type'        => ProductType::SERVICE,
                'unit_id'     => $unit->id,
                'vat_rate_id' => $vatRate->id,
            ],
        ];

        foreach ($products as $product) {
            $tenant->products()->create($product);
        }
    }

    protected function createInvoices(Tenant $tenant): void
    {
        /** @var NumberingTemplate $template */
        $template = NumberingTemplate::where('invoice_type', InvoiceType::Basic->value)->where('is_default', true)->first();

        // Generate invoices for the previous year (all 12 months)
        $previousYear = now()->subYear()->startOfYear();

        for ($i = 0; $i < 12; ++$i) {
            $invoiceDate = $previousYear->copy()->addMonths($i);

            $tenant->invoices()->create(
                Invoice::factory()
                    ->soldServicesToNasa($tenant)
                    ->withDates($invoiceDate, $invoiceDate->copy()->addDays(14))
                    ->make([
                        'number'                => "TEST/{$template->generateNextNumber()}",
                        'type'                  => InvoiceType::Basic,
                        'status'                => InvoiceStatus::COMPLETED,
                        'numbering_template_id' => $template->id,
                        'created_at'            => $invoiceDate,
                    ])->toArray()
            );
        }

        // Generate invoices for the current year up to current month
        $currentYear  = now()->startOfYear();
        $currentMonth = now()->month;

        $contractor = Contractor::where('tenant_id', $tenant->id)->first();
        $product    = Product::where('tenant_id', $tenant->id)->first();

        for ($i = 0; $i < $currentMonth; ++$i) {
            $invoiceDate = $currentYear->copy()->addMonths($i);

            $tenant->invoices()->create(
                Invoice::factory()
                    ->soldServicesToNasa($tenant)
                    ->withDates($invoiceDate, $invoiceDate->copy()->addDays(14))
                    ->make([
                        'number'                => "TEST/{$template->generateNextNumber()}",
                        'type'                  => InvoiceType::Export,
                        'numbering_template_id' => $template->id,
                        'created_at'            => $invoiceDate,
                    ])->toArray()
            );

            $tenant->invoices()->create(
                Invoice::factory()
                    ->soldServicesToContractor($tenant, $contractor, $product)
                    ->withDates($invoiceDate, $invoiceDate->copy()->addDays(14))
                    ->make([
                        'number'                => "TEST/{$template->generateNextNumber()}",
                        'type'                  => InvoiceType::Basic,
                        'numbering_template_id' => $template->id,
                        'created_at'            => $invoiceDate,
                    ])->toArray()
            );
        }

        $tenant->invoices()->create(
            Invoice::factory()
                ->soldServicesToContractor($tenant, $contractor, $product)
                ->withDates(now(), now()->addDays(14))
                ->make([
                    'number'                => "DEMO/{$template->generateNextNumber()}",
                    'type'                  => InvoiceType::Basic,
                    'numbering_template_id' => $template->id,
                    'status'                => InvoiceStatus::ISSUED,
                    'created_at'            => now(),
                ])->toArray()
        );
    }

    protected function createExpenses(Tenant $tenant): void
    {
        // Generate invoices for the current year up to current month
        $currentYear  = now()->startOfYear();
        $currentMonth = now()->month;

        for ($i = 0; $i < $currentMonth; ++$i) {
            $invoiceDate = $currentYear->copy()->addMonths($i);

            $tenant->expenses()->create(
                Expense::factory()
                    ->receivedFromOvh($tenant)
                    ->withDates($invoiceDate, $invoiceDate->copy()->addDays(14))
                    ->make([
                        'type'       => InvoiceType::Import,
                        'status'     => InvoiceStatus::ISSUED,
                        'created_at' => $invoiceDate,
                    ])->toArray()
            );

            $invoiceDate = $invoiceDate->copy()->addDays(7);

            $tenant->expenses()->create(
                Expense::factory()
                    ->receivedFromBp($tenant)
                    ->withDates($invoiceDate, $invoiceDate->copy()->addDays(14))
                    ->make([
                        'type'       => InvoiceType::Basic,
                        'status'     => InvoiceStatus::ISSUED,
                        'created_at' => $invoiceDate,
                    ])->toArray()
            );
        }
    }

    protected function createApiKeys(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            Tenant::bypassTenant($tenant->id, function () use ($tenant) {
                $users = $tenant->users;

                /** @var Collection<int, User> $users */
                foreach ($users as $user) {
                    $user->apiKeys()->create([
                        'tenant_id' => $tenant->id,
                        'user_id'   => $user->id,
                        'name'      => 'Default API Key',
                        'key'       => Str::random(64),
                        'scopes'    => ['read', 'write'],
                        'is_active' => false,
                    ]);
                }
            });
        }
    }

    protected function createOrganizationUnits(Tenant $tenant, OrganizationUnit $rootUnit): void
    {
        $units = [
            'IT Department',
            'HR Department',
            'Finance Department',
        ];

        foreach ($units as $unit) {
            $unit = OrganizationUnit::create([
                'id'         => Ulid::deterministic([$tenant->id, $unit]),
                'tenant_id'  => $tenant->id,
                'parent_id'  => $rootUnit->id,
                'name'       => $unit,
                'code'       => Str::slug($unit),
            ]);

            $categories = [
                DefaultPositionCategory::Director,
                DefaultPositionCategory::Employee,
                DefaultPositionCategory::Trainee,
            ];

            foreach ($categories as $category) {
                $position = $unit->positions()->create([
                    'id'                   => Ulid::deterministic([$tenant->id, $unit, 'position', $category->value]),
                    'tenant_id'            => $tenant->id,
                    'organization_unit_id' => $unit->id,
                    'position_category_id' => PositionCategory::where('name', $category->value)->first()->id,
                    'name'                 => $category->value,
                    'description'          => $category->value . ' position',
                    'is_active'            => true,
                    'is_director'          => DefaultPositionCategory::Director === $category,
                    'is_learning'          => DefaultPositionCategory::Trainee === $category,
                ]);
            }
        }
    }
}
