<?php

namespace App\Providers;

use App\Domain\Auth\Events\UserCreated;
use App\Domain\Common\Support\ColumnTypeCache;
use App\Domain\Tenant\Listeners\CreateTenantForNewUser;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(MigrationsEnded::class, function () {
            ColumnTypeCache::clearAll();
        });

        Event::listen(
            UserCreated::class,
            [CreateTenantForNewUser::class, 'handle']
        );
    }
}
