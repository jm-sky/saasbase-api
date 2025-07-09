<?php

use App\Domain\Financial\Controllers\FinancialReportController;
use App\Domain\Financial\Controllers\PKWiUClassificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->prefix('financial-reports')->group(function () {
    Route::get('balance-widget', [FinancialReportController::class, 'balanceWidget'])->name('financial-reports.balance-widget');
    Route::get('revenue-widget', [FinancialReportController::class, 'revenueWidget'])->name('financial-reports.revenue-widget');
    Route::get('expenses-widget', [FinancialReportController::class, 'expensesWidget'])->name('financial-reports.expenses-widget');
    Route::get('overview-widget', [FinancialReportController::class, 'overviewWidget'])->name('financial-reports.overview-widget');
});

// PKWiU Classification Routes (global, not tenant-specific)
Route::middleware(['auth:api', 'is_active'])->prefix('pkwiu')->group(function () {
    Route::get('/', [PKWiUClassificationController::class, 'index'])->name('pkwiu.index');
    Route::get('/tree', [PKWiUClassificationController::class, 'tree'])->name('pkwiu.tree');
    Route::get('/search', [PKWiUClassificationController::class, 'search'])->name('pkwiu.search');
    Route::get('/suggest', [PKWiUClassificationController::class, 'suggest'])->name('pkwiu.suggest');
    Route::post('/validate', [PKWiUClassificationController::class, 'validateCode'])->name('pkwiu.validate');
    Route::post('/validate-invoice', [PKWiUClassificationController::class, 'validateInvoiceBody'])->name('pkwiu.validate-invoice');
    Route::get('/{code}', [PKWiUClassificationController::class, 'show'])->name('pkwiu.show');
});
