<?php

namespace App\Providers;

use App\Domain\Auth\Events\UserCreated;
use App\Domain\Common\Support\ColumnTypeCache;
use App\Domain\Tenant\Listeners\CreateTenantForNewUser;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        // Register morph map for allocation dimensions
        Relation::morphMap([
            'HA'  => \App\Domain\Auth\Models\User::class,
            'LO'  => \App\Domain\Common\Models\AllocationLocation::class,
            'PD'  => \App\Domain\Products\Models\AllocationProductCategory::class,
            'PR'  => \App\Domain\Projects\Models\Project::class,
            'RS'  => \App\Domain\Financial\Models\AllocationRevenueType::class,
            'RTR' => \App\Domain\Financial\Models\AllocationTransactionType::class,
            'RY'  => \App\Domain\Financial\Models\AllocationCostType::class,
            'ST'  => \App\Domain\Tenant\Models\OrganizationUnit::class,
            'TP'  => \App\Domain\Financial\Models\AllocationRelatedTransactionCategory::class,
            'UM'  => \App\Domain\Common\Models\AllocationContractType::class,
            'UR'  => \App\Domain\Common\Models\AllocationEquipmentType::class,
        ]);
    }
}
