<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\StripeClient;

class SetupStripeTestData extends Command
{
    protected $signature = 'stripe:setup-test-data';

    protected $description = 'Set up test products and prices in Stripe';

    public function handle(StripeClient $stripe)
    {
        $this->info('Setting up test data in Stripe...');

        try {
            // Create test product
            $product = $stripe->products->create([
                'name'        => 'Test Product',
                'description' => 'A test product for development',
            ]);

            $this->info('✅ Created test product: ' . $product->id);

            // Create monthly price
            $monthlyPrice = $stripe->prices->create([
                'product'     => $product->id,
                'unit_amount' => 1000, // $10.00
                'currency'    => 'usd',
                'recurring'   => [
                    'interval' => 'month',
                ],
            ]);

            $this->info('✅ Created monthly price: ' . $monthlyPrice->id);

            // Create yearly price
            $yearlyPrice = $stripe->prices->create([
                'product'     => $product->id,
                'unit_amount' => 10000, // $100.00
                'currency'    => 'usd',
                'recurring'   => [
                    'interval' => 'year',
                ],
            ]);

            $this->info('✅ Created yearly price: ' . $yearlyPrice->id);

            // Create test customer
            $customer = $stripe->customers->create([
                'email' => 'test@example.com',
                'name'  => 'Test Customer',
            ]);

            $this->info('✅ Created test customer: ' . $customer->id);

            // Create test payment method
            $paymentMethod = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number'    => '4242424242424242',
                    'exp_month' => 12,
                    'exp_year'  => date('Y') + 1,
                    'cvc'       => '123',
                ],
            ]);

            $this->info('✅ Created test payment method: ' . $paymentMethod->id);

            // Attach payment method to customer
            $stripe->paymentMethods->attach($paymentMethod->id, [
                'customer' => $customer->id,
            ]);

            $this->info('✅ Attached payment method to customer');

            $this->info("\nTest Data Summary:");
            $this->info('Product ID: ' . $product->id);
            $this->info('Monthly Price ID: ' . $monthlyPrice->id);
            $this->info('Yearly Price ID: ' . $yearlyPrice->id);
            $this->info('Customer ID: ' . $customer->id);
            $this->info('Payment Method ID: ' . $paymentMethod->id);

            $this->info("\nYou can use these IDs in your tests and development.");
        } catch (\Exception $e) {
            $this->error('❌ Failed to set up test data:');
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
