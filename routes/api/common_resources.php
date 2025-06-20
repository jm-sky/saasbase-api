<?php

use App\Domain\Common\Controllers\CountryController;
use App\Domain\Exchanges\Controllers\CurrencyController;
use App\Domain\Exchanges\Controllers\ExchangeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
    Route::get('currencies', [CurrencyController::class, 'index'])->name('currencies.index');
    Route::apiResource('exchanges', ExchangeController::class)->only(['index', 'show']);
    Route::get('exchanges/{exchange}/rates', [ExchangeController::class, 'getRates']);
});
