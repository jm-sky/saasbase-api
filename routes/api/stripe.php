<?php

use App\Domain\Subscription\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('stripe/webhook', StripeWebhookController::class)
    ->name('stripe.webhook')
    ->middleware('stripe.webhook');
