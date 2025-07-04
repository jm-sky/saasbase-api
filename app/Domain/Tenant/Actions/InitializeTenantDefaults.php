<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Enums\TagColor;
use App\Domain\Common\Models\DefaultMeasurementUnit;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Models\Tag;
use App\Domain\Projects\Models\DefaultProjectStatus;
use App\Domain\Projects\Models\ProjectStatus;
use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Tenant\Enums\DefaultPositionCategory;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\PositionCategory;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;

class InitializeTenantDefaults
{
    public static array $defaultTags = [
        'VIP' => [
            'color' => TagColor::DEFAULT,
        ],
        'Test' => [
            'color' => TagColor::DEFAULT,
        ],
        'To Do' => [
            'color' => TagColor::INFO,
        ],
        'Warning' => [
            'color' => TagColor::DANGER,
        ],
        'Critical' => [
            'color' => TagColor::DANGER_INTENSE,
        ],
    ];

    public function execute(Tenant $tenant, ?User $owner = null): void
    {
        Tenant::bypassTenant($tenant->id, function () use ($tenant, $owner) {
            $this->createDefaultPositionCategories($tenant);
            $this->createOrganizationUnits($tenant, $owner);
            $this->seedDefaultMeasurementUnits($tenant);
            $this->seedDefaultTags($tenant);
            $this->createSubscription($tenant);
        });
    }

    public function createDefaultPositionCategories(Tenant $tenant): void
    {
        $defaultCategories = [
            [
                'name'        => DefaultPositionCategory::Director->value,
                'slug'        => Str::slug(DefaultPositionCategory::Director->value),
                'description' => 'Leadership positions',
                'sort_order'  => 1,
            ],
            [
                'name'        => DefaultPositionCategory::Manager->value,
                'slug'        => Str::slug(DefaultPositionCategory::Manager->value),
                'description' => 'Management positions',
                'sort_order'  => 2,
            ],
            [
                'name'        => DefaultPositionCategory::Employee->value,
                'slug'        => Str::slug(DefaultPositionCategory::Employee->value),
                'description' => 'Regular employee positions',
                'sort_order'  => 3,
            ],
            [
                'name'        => DefaultPositionCategory::Trainee->value,
                'slug'        => Str::slug(DefaultPositionCategory::Trainee->value),
                'description' => 'Learning and training positions',
                'sort_order'  => 4,
            ],
        ];

        foreach ($defaultCategories as $category) {
            PositionCategory::firstOrCreate([
                'tenant_id' => $tenant->id,
                'name'      => $category['name'],
            ], $category);
        }
    }

    public function createOrganizationUnits(Tenant $tenant, ?User $owner = null): OrganizationUnit
    {
        $rootUnit = $this->createRootOrganizationUnit($tenant, $owner);

        $this->createTechnicalOrganizationUnits($tenant, $rootUnit);

        return $rootUnit;
    }

    public function createRootOrganizationUnit(Tenant $tenant, ?User $owner = null): OrganizationUnit
    {
        $rootUnit = CreateRootOrganizationUnit::createUnit($tenant);

        CreateRootOrganizationUnit::createPositions($rootUnit);

        if ($owner) {
            CreateRootOrganizationUnit::createOwner($tenant, $rootUnit);
        }

        return $rootUnit;
    }

    public function createTechnicalOrganizationUnits(Tenant $tenant, OrganizationUnit $rootUnit): void
    {
        CreateTechnicalOrganizationUnits::createUnassignedUnit($tenant, $rootUnit);
        CreateTechnicalOrganizationUnits::createFormerEmployeesUnit($tenant, $rootUnit);
    }

    public function seedDefaultMeasurementUnits(Tenant $tenant): void
    {
        $defaultUnits = DefaultMeasurementUnit::where('is_default', true)->get();

        foreach ($defaultUnits as $unit) {
            MeasurementUnit::withoutTenant()->firstOrCreate([
                'tenant_id' => $tenant->id,
                'code'      => $unit->code,
                'name'      => $unit->name,
                'category'  => $unit->category,
            ]);
        }
    }

    public function seedDefaultProjectStatuses(Tenant $tenant): void
    {
        $defaultStatuses = DefaultProjectStatus::where('is_default', true)->get();

        foreach ($defaultStatuses as $status) {
            ProjectStatus::withoutTenant()->firstOrCreate([
                'tenant_id'  => $tenant->id,
                'name'       => $status->name,
                'color'      => $status->color,
                'sort_order' => $status->sort_order,
                'is_default' => $status->is_default,
            ]);
        }
    }

    protected function createSubscription(Tenant $tenant)
    {
        $subscriptionPlan = SubscriptionPlan::where('name', 'Free')->first();

        if (!$subscriptionPlan) {
            return;
        }

        $tenant->subscription()->create([
            'id'                     => (string) Str::ulid(),
            'subscription_plan_id'   => $subscriptionPlan->id,
            'stripe_subscription_id' => null,
            'status'                 => SubscriptionStatus::ACTIVE,
            'current_period_start'   => now(),
            'current_period_end'     => now()->addYear(),
            'cancel_at_period_end'   => false,
        ]);
    }

    public function seedDefaultTags(Tenant $tenant): void
    {
        foreach (self::$defaultTags as $name => $meta) {
            Tag::withoutTenant()->firstOrCreate([
                'tenant_id' => $tenant->id,
                'name'      => $name,
                'slug'      => Str::slug($name),
            ], [
                'color'     => $meta['color'],
            ]);
        }
    }
}
