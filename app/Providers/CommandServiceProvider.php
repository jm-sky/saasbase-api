<?php

namespace App\Providers;

use App\Services\CompanyLookup\Commands\CompanyLookupCommand;
use App\Services\ViesLookup\Commands\ViesLookupCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CompanyLookupCommand::class,
                ViesLookupCommand::class,
            ]);
        }
    }
}
