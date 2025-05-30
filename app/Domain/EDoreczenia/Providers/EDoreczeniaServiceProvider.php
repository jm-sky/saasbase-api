<?php

namespace App\Domain\EDoreczenia\Providers;

use App\Domain\EDoreczenia\Contracts\EDoreczeniaProviderInterface;
use App\Domain\EDoreczenia\Services\EDoreczeniaProviderManager;
use Illuminate\Support\ServiceProvider;

class EDoreczeniaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/edoreczenia.php',
            'edoreczenia'
        );

        $this->app->singleton(EDoreczeniaProviderManager::class, function ($app) {
            return new EDoreczeniaProviderManager();
        });

        $this->app->bind(EDoreczeniaProviderInterface::class, function ($app) {
            $manager = $app->make(EDoreczeniaProviderManager::class);

            return $manager->getProvider(config('edoreczenia.default_provider'));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../../config/edoreczenia.php' => config_path('edoreczenia.php'),
        ], 'edoreczenia-config');
    }
}
