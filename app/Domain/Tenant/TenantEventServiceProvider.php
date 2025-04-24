<?php

namespace App\Domain\Tenant;

use App\Domain\Tenant\Events\TenantCreated;
use App\Domain\Tenant\Listeners\CreateDefaultsForTenant;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
