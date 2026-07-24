<?php

use App\Http\Middleware\EnsureSessionNotRevoked;
use App\Http\Middleware\EnsureTwoFactorVerified;
use App\Http\Middleware\IsActive;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsInTenant;
use App\Http\Middleware\SetLocaleFromHeader;
use App\Http\Middleware\StripeWebhook;
use App\Http\Middleware\VerifyHealthDetailsToken;
use App\Services\NBP\Jobs\ImportExchangeRatesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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
            SetLocaleFromHeader::class,
        ]);

        $middleware->alias([
            'stripe.webhook' => StripeWebhook::class,
            'health.details' => VerifyHealthDetailsToken::class,
            'is_active' => IsActive::class,
            'is_in_tenant' => IsInTenant::class,
            'is_admin' => IsAdmin::class,
            'mfa' => EnsureTwoFactorVerified::class,
            'session.active' => EnsureSessionNotRevoked::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new ImportExchangeRatesJob)
            ->weekdays()
            ->dailyAt('18:00')
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
