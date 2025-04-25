<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal
                || $entry->isReportableException()
                || $entry->isFailedRequest()
                || $entry->isFailedJob()
                || $entry->isScheduledTask()
                || $entry->hasMonitoredTag();
        });

        // Ensure that Telescope uses the correct guard
        Telescope::auth(function ($request) {
            return auth()->guard('web')->check() && auth()->user()->isAdmin(); // Use 'web' guard and check if user is admin
        });
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user = null) {
            return $user && $user->isAdmin(); // Check if the user is an admin
        });
    }
}
