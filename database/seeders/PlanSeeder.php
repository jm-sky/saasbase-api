<?php

namespace Database\Seeders;

use App\Domain\Subscription\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'              => 'Basic',
                'description'       => 'Basic plan for small businesses',
                'stripe_product_id' => 'prod_basic',
                'stripe_price_id'   => 'price_basic_monthly',
                'interval'          => 'monthly',
                'price'             => 49.99,
            ],
            [
                'name'              => 'Pro',
                'description'       => 'Professional plan for growing businesses',
                'stripe_product_id' => 'prod_pro',
                'stripe_price_id'   => 'price_pro_monthly',
                'interval'          => 'monthly',
                'price'             => 99.99,
            ],
            [
                'name'              => 'Full',
                'description'       => 'Full access plan with all features',
                'stripe_product_id' => 'prod_full',
                'stripe_price_id'   => 'price_full_monthly',
                'interval'          => 'monthly',
                'price'             => 199.99,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
