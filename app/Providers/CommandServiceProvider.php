<?php

namespace App\Providers;

use App\Services\RegonLookup\Commands\RegonLookupCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RegonLookupCommand::class,
            ]);
        }
    }
}
