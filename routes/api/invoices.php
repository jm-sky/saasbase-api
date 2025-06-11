<?php

use App\Domain\Invoice\Controllers\InvoiceController;
use App\Domain\Invoice\Controllers\InvoiceShareTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::get('invoices/search', [InvoiceController::class, 'search'])->name('invoices.search');
    Route::get('invoices/export', [InvoiceController::class, 'export']);
    Route::apiResource('invoices', InvoiceController::class);

    Route::apiResource('invoices.share-tokens', InvoiceShareTokenController::class)->only(['index', 'store', 'destroy']);
});
