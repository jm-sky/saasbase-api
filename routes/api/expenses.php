<?php

use App\Domain\Expense\Controllers\ExpenseAttachmentsController;
use App\Domain\Expense\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::get('expenses/search', [ExpenseController::class, 'search'])->name('expenses.search');
    Route::get('expenses/export', [ExpenseController::class, 'export']);
    Route::post('expenses/upload-for-ocr', [ExpenseController::class, 'uploadForOcr']);
    Route::post('expenses/{expense}/start-ocr', [ExpenseController::class, 'startOcr']);
    Route::apiResource('expenses/{expense}/attachments', ExpenseAttachmentsController::class);
    Route::apiResource('expenses', ExpenseController::class);
});
