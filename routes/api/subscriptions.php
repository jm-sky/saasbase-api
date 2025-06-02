<?php

use App\Domain\Subscription\Controllers\AddonPackageController;
use App\Domain\Subscription\Controllers\AddonPurchaseController;
use App\Domain\Subscription\Controllers\SubscriptionController;
use App\Domain\Subscription\Controllers\SubscriptionInvoiceController;
use App\Domain\Subscription\Controllers\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::prefix('subscription-plans')->group(function () {
    Route::get('/', [SubscriptionPlanController::class, 'index']);
    Route::get('/{id}', [SubscriptionPlanController::class, 'show']);
});

Route::prefix('subscriptions')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index']);
    Route::post('/', [SubscriptionController::class, 'store']);
    Route::get('/{id}', [SubscriptionController::class, 'show']);
    Route::put('/{id}', [SubscriptionController::class, 'update']);
    Route::delete('/{id}', [SubscriptionController::class, 'destroy']);
});

Route::prefix('addon-packages')->group(function () {
    Route::get('/', [AddonPackageController::class, 'index']); // List available addons
    Route::get('/{id}', [AddonPackageController::class, 'show']); // Show pricing and details
});

Route::prefix('addon-purchases')->group(function () {
    Route::get('/', [AddonPurchaseController::class, 'index']); // List active addons (AddonPurchase)
    Route::post('/', [AddonPurchaseController::class, 'store']); // Purchase addon (AddonPurchase)
    Route::get('/{id}', [AddonPurchaseController::class, 'show']); // Show expiration dates
});

Route::prefix('subscription-invoices')->group(function () {
    Route::get('/', [SubscriptionInvoiceController::class, 'index']);
    Route::get('/{id}', [SubscriptionInvoiceController::class, 'show']);
});
