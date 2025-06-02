<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class StripeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function ($app) {
            return new StripeClient(config('stripe.secret'));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/stripe.php' => config_path('stripe.php'),
        ], 'stripe-config');

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/stripe.php',
            'stripe'
        );
    }
}
