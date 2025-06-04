<?php

namespace Database\Seeders;

use App\Domain\Subscription\Enums\FeatureName;
use App\Domain\Subscription\Models\Feature;
use App\Domain\Subscription\Models\PlanFeature;
use App\Domain\Subscription\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFreePlan();
        $this->seedBasicPlan();
        $this->seedProPlan();
        $this->seedFullPlan();
    }

    private function seedFreePlan(): void
    {
        $plan = SubscriptionPlan::where('name', 'Free')->first();

        if (!$plan) {
            return;
        }

        $features = [
            FeatureName::MAX_USERS->value               => '1',
            FeatureName::STORAGE_MB->value              => '500',
            FeatureName::MAX_INVOICES->value            => '3',
            FeatureName::GUS_VIES_REQUESTS->value       => '10',
            FeatureName::KSEF_INTEGRATION->value        => 'false',
            FeatureName::EDORECZENIA_INTEGRATION->value => 'false',
            FeatureName::UNLIMITED_GUS_VIES->value      => 'false',
            FeatureName::AUTO_CONTRACTOR_LOGO->value    => 'false',
        ];

        $this->createPlanFeatures($plan, $features);
    }

    private function seedBasicPlan(): void
    {
        $plan = SubscriptionPlan::where('name', 'Basic')->first();

        if (!$plan) {
            return;
        }

        $features = [
            FeatureName::MAX_USERS->value               => '5',
            FeatureName::STORAGE_MB->value              => '1000',
            FeatureName::MAX_INVOICES->value            => '10',
            FeatureName::GUS_VIES_REQUESTS->value       => '25',
            FeatureName::KSEF_INTEGRATION->value        => 'false',
            FeatureName::EDORECZENIA_INTEGRATION->value => 'false',
            FeatureName::UNLIMITED_GUS_VIES->value      => 'false',
            FeatureName::AUTO_CONTRACTOR_LOGO->value    => 'false',
        ];

        $this->createPlanFeatures($plan, $features);
    }

    private function seedProPlan(): void
    {
        $plan = SubscriptionPlan::where('name', 'Pro')->first();

        if (!$plan) {
            return;
        }

        $features = [
            FeatureName::MAX_USERS->value               => '10',
            FeatureName::STORAGE_MB->value              => '10000',
            FeatureName::MAX_INVOICES->value            => '100',
            FeatureName::GUS_VIES_REQUESTS->value       => '100',
            FeatureName::KSEF_INTEGRATION->value        => 'true',
            FeatureName::EDORECZENIA_INTEGRATION->value => 'true',
            FeatureName::UNLIMITED_GUS_VIES->value      => 'false',
            FeatureName::AUTO_CONTRACTOR_LOGO->value    => 'true',
        ];

        $this->createPlanFeatures($plan, $features);
    }

    private function seedFullPlan(): void
    {
        $plan = SubscriptionPlan::where('name', 'Full')->first();

        if (!$plan) {
            return;
        }

        $features = [
            FeatureName::MAX_USERS->value               => 'unlimited',
            FeatureName::STORAGE_MB->value              => '100000',
            FeatureName::MAX_INVOICES->value            => 'unlimited',
            FeatureName::GUS_VIES_REQUESTS->value       => 'unlimited',
            FeatureName::KSEF_INTEGRATION->value        => 'true',
            FeatureName::EDORECZENIA_INTEGRATION->value => 'true',
            FeatureName::UNLIMITED_GUS_VIES->value      => 'true',
            FeatureName::AUTO_CONTRACTOR_LOGO->value    => 'true',
        ];

        $this->createPlanFeatures($plan, $features);
    }

    private function createPlanFeatures(SubscriptionPlan $plan, array $features): void
    {
        foreach ($features as $name => $value) {
            $feature = Feature::where('name', $name)->first();

            if (!$feature) {
                continue;
            }

            PlanFeature::create([
                'subscription_plan_id' => $plan->id,
                'feature_id'           => $feature->id,
                'value'                => $value,
            ]);
        }
    }
}
