<?php

namespace App\Domain\Tenant;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Domain\Tenant\Events\TenantCreated;
use App\Domain\Tenant\Listeners\CreateDefaultsForTenant;

class TenantEventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            TenantCreated::class,
            CreateDefaultsForTenant::class
        );
    }
}
