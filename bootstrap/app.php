<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->trustProxies(headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_PREFIX |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->appendToGroup('api', [
            \App\Http\Middleware\SetLocaleFromHeader::class,
        ]);

        $middleware->alias([
            'stripe.webhook' => \App\Http\Middleware\StripeWebhook::class,
            'is_active' => \App\Http\Middleware\IsActive::class,
            'is_in_tenant' => \App\Http\Middleware\IsInTenant::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new \App\Services\NBP\Jobs\ImportExchangeRatesJob())
            ->weekdays()
            ->dailyAt('18:00')
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
