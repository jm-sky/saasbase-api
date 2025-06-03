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
                'name'              => 'Free',
                'description'       => 'Free plan for discovering app features',
                'stripe_product_id' => null,
                'stripe_price_id'   => null,
                'interval'          => 'monthly',
                'price'             => 0.00,
            ],
            [
                'name'              => 'Basic',
                'description'       => 'Basic plan for small businesses',
                'stripe_product_id' => 'prod_SOGm0E5JZd3VmE',
                'stripe_price_id'   => 'price_1RTUEXK5z7ZAQPXQ076ndzjF',
                'interval'          => 'monthly',
                'price'             => 50.00,
            ],
            [
                'name'              => 'Pro',
                'description'       => 'Professional plan for growing businesses',
                'stripe_product_id' => 'prod_SOGnMpksxZBBrc',
                'stripe_price_id'   => 'price_1RTUFRK5z7ZAQPXQmd5XG0lj',
                'interval'          => 'monthly',
                'price'             => 100.00,
            ],
            [
                'name'              => 'Full',
                'description'       => 'Full access plan with all features',
                'stripe_product_id' => 'prod_SQiYAwM7XMMPSD',
                'stripe_price_id'   => 'price_1RVr7JK5z7ZAQPXQkc7kIQGt',
                'interval'          => 'monthly',
                'price'             => 250.00,
                'is_active'         => false,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
