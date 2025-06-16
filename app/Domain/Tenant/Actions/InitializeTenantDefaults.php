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
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
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
        $this->createRootOrganizationUnit($tenant, $owner);
        $this->seedDefaultMeasurementUnits($tenant);
        $this->createSubscription($tenant);
        $this->seedDefaultTags($tenant);
        // 3. (Optional) Add more default setup here
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

    public function createRootOrganizationUnit(Tenant $tenant, ?User $owner = null): void
    {
        $rootUnit = OrganizationUnit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'parent_id' => null],
            [
                'id'         => (string) Str::ulid(),
                'name'       => $tenant->name,
                'short_name' => Str::slug($tenant->name),
            ]
        );

        if ($owner) {
            OrgUnitUser::firstOrCreate(
                [
                    'organization_unit_id' => $rootUnit->id,
                    'user_id'              => $owner->id,
                ],
                [
                    'id'   => (string) Str::ulid(),
                    'role' => OrgUnitRole::Owner,
                ]
            );
        }
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
