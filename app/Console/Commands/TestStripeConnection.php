<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\StripeClient;

class TestStripeConnection extends Command
{
    protected $signature = 'stripe:test-connection';

    protected $description = 'Test the connection to Stripe API';

    public function handle(StripeClient $stripe)
    {
        $this->info('Testing Stripe connection...');

        try {
            // Test API connection by fetching account info
            $account = $stripe->accounts->retrieve('self');

            $this->info('✅ Successfully connected to Stripe!');
            $this->info('Account ID: ' . $account->id);
            $this->info('Account Name: ' . ($account->business_profile->name ?? 'Not set'));

            // Test webhook configuration
            if (config('stripe.webhook_secret')) {
                $this->info('✅ Webhook secret is configured');
            } else {
                $this->warn('⚠️ Webhook secret is not configured');
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to connect to Stripe:');
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
