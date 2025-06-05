<?php

use Illuminate\Support\Facades\Route;

Route::post('stripe/webhook', App\Domain\Subscription\Controllers\StripeWebhookController::class)
    ->name('stripe.webhook')
    ->middleware('stripe.webhook')
;
