<?php

use App\Domain\Utils\Controllers\CompanyLookupController;
use App\Domain\Utils\Controllers\IbanInfoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::get('utils/company-lookup', [CompanyLookupController::class, 'lookup']);
    Route::get('utils/iban-info', IbanInfoController::class);
});
