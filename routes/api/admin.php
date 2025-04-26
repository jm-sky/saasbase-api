<?php

use App\Domain\Admin\Contractors\Controllers\AdminContractorController;
use App\Domain\Admin\Products\Controllers\AdminProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:api', 'is_admin'])->group(function () {
    Route::apiResource('contractors', AdminContractorController::class);
    Route::apiResource('products', AdminProductController::class);
});
