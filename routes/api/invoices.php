<?php

use App\Domain\Invoice\Controllers\InvoiceController;
use App\Domain\Invoice\Controllers\InvoiceShareTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('invoices/search', [InvoiceController::class, 'search'])->name('invoices.search');

    Route::apiResource('invoices.share-tokens', InvoiceShareTokenController::class)->only(['index', 'store', 'destroy']);
});
