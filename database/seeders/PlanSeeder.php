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
                'is_active'         => true,
            ],
            [
                'name'              => 'Basic',
                'description'       => 'Basic plan for small businesses',
                'stripe_product_id' => 'prod_SQnTJVkD5vrPcT',
                'is_active'         => true,
                'prices'            => [
                    [
                        'stripe_price_id' => 'price_1RVvsXK5z7ZAQPXQWCF0aUpu',
                        'billing_period'  => 'monthly',
                        'price_cents'     => 5000,
                        'currency'        => 'PLN',
                        'is_active'       => true,
                    ],
                    [
                        'stripe_price_id' => 'price_1RVwCfK5z7ZAQPXQSJ8TcWn0',
                        'billing_period'  => 'yearly',
                        'price_cents'     => 48000,
                        'currency'        => 'PLN',
                        'is_active'       => true,
                    ],
                ],
            ],
            [
                'name'              => 'Pro',
                'description'       => 'Professional plan for growing businesses',
                'stripe_product_id' => 'prod_SQnTfFLbNzbVob',
                'is_active'         => true,
                'prices'            => [
                    [
                        'stripe_price_id' => 'price_1RVvsoK5z7ZAQPXQ8y62KYS1',
                        'billing_period'  => 'monthly',
                        'price_cents'     => 10000,
                        'currency'        => 'PLN',
                        'is_active'       => true,
                    ],
                    [
                        'stripe_price_id' => 'price_1RVwDTK5z7ZAQPXQzDMqcZiX',
                        'billing_period'  => 'yearly',
                        'price_cents'     => 96000,
                        'currency'        => 'PLN',
                        'is_active'       => true,
                    ],
                ],
            ],
            [
                'name'              => 'Full',
                'description'       => 'Full access plan with all features',
                'stripe_product_id' => 'prod_SQnUTuCMtyRhWF',
                'is_active'         => false,
                'prices'            => [
                    [
                        'stripe_price_id' => 'price_1RVvt6K5z7ZAQPXQxsB5YrC9',
                        'billing_period'  => 'monthly',
                        'price_cents'     => 25000,
                        'currency'        => 'PLN',
                        'is_active'       => true,
                    ],
                    [
                        'stripe_price_id' => 'price_1RVwEYK5z7ZAQPXQvvUWhAQW',
                        'billing_period'  => 'yearly',
                        'price_cents'     => 240000,
                        'currency'        => 'PLN',
                        'is_active'       => true,
                    ],
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $prices = $planData['prices'] ?? [];
            unset($planData['prices']);

            $plan = SubscriptionPlan::create($planData);

            foreach ($prices as $priceData) {
                $plan->prices()->create($priceData);
            }
        }
    }
}
