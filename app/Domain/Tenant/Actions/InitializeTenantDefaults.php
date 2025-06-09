<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\DefaultMeasurementUnit;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrgUnitUser;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;

class InitializeTenantDefaults
{
    public function execute(Tenant $tenant, ?User $owner = null): void
    {
        $this->createRootOrganizationUnit($tenant, $owner);
        $this->seedDefaultMeasurementUnits($tenant);
        $this->createSubscription($tenant);
        // 3. (Optional) Add more default setup here
    }

    protected function createSubscription(Tenant $tenant)
    {
        $tenant->subscription()->create([
            'id'                     => (string) Str::ulid(),
            'subscription_plan_id'   => SubscriptionPlan::where('name', 'Free')->firstOrFail()->id,
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
}
