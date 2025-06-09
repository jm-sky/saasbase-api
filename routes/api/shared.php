<?php

use App\Domain\Common\Controllers\MeasurementUnitController;
use App\Domain\Common\Controllers\VatRateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::apiResource('vat-rates', VatRateController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('measurement-units', MeasurementUnitController::class)->only(['index', 'store', 'destroy']);
});
