<?php

namespace App\Providers;

use App\Services\EDoreczenia\EDoreczeniaProviderManager;
use Illuminate\Support\ServiceProvider;

class EDoreczeniaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EDoreczeniaProviderManager::class, function ($app) {
            return new EDoreczeniaProviderManager();
        });
    }

    public function boot(): void
    {
    }
}
