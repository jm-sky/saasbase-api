<?php

use App\Domain\Financial\Controllers\FinancialReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->prefix('financial-reports')->group(function () {
    Route::get('balance-widget', [FinancialReportController::class, 'balanceWidget'])->name('financial-reports.balance-widget');
    Route::get('revenue-widget', [FinancialReportController::class, 'revenueWidget'])->name('financial-reports.revenue-widget');
    Route::get('expenses-widget', [FinancialReportController::class, 'expensesWidget'])->name('financial-reports.expenses-widget');
    Route::get('overview-widget', [FinancialReportController::class, 'overviewWidget'])->name('financial-reports.overview-widget');
});
