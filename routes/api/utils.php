<?php

use App\Http\Controllers\Domain\Utils\Controllers\CompanyLookupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::get('utils/company-lookup', [CompanyLookupController::class, 'lookup']);
});
