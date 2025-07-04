<?php

use App\Domain\Expense\Controllers\DimensionConfigurationController;
use App\Domain\Expense\Controllers\ExpenseAllocationController;
use App\Domain\Expense\Controllers\ExpenseApprovalController;
use App\Domain\Expense\Controllers\ExpenseAttachmentsController;
use App\Domain\Expense\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::get('expenses/search', [ExpenseController::class, 'search'])->name('expenses.search');
    Route::get('expenses/export', [ExpenseController::class, 'export']);
    Route::post('expenses/upload-for-ocr', [ExpenseController::class, 'uploadForOcr']);
    Route::post('expenses/{expense}/start-ocr', [ExpenseController::class, 'startOcr']);

    // Expense allocation routes
    Route::get('expenses/{expense}/allocations', [ExpenseAllocationController::class, 'index'])->name('expenses.allocations.index');
    Route::post('expenses/{expense}/allocations', [ExpenseAllocationController::class, 'store'])->name('expenses.allocations.store');
    Route::post('expenses/{expense}/allocations/auto', [ExpenseAllocationController::class, 'autoAllocate'])->name('expenses.allocations.auto');
    Route::get('expenses/{expense}/allocations/suggestions', [ExpenseAllocationController::class, 'suggestions'])->name('expenses.allocations.suggestions');
    Route::delete('expenses/{expense}/allocations/clear', [ExpenseAllocationController::class, 'clear'])->name('expenses.allocations.clear');
    Route::delete('expenses/{expense}/allocations/{allocation}', [ExpenseAllocationController::class, 'destroy'])->name('expenses.allocations.destroy');

    // Expense approval routes
    Route::get('pending-approvals', [ExpenseApprovalController::class, 'pendingApprovals'])->name('approvals.pending');
    Route::get('approval-history', [ExpenseApprovalController::class, 'approvalHistory'])->name('approvals.history');
    Route::get('expenses/{expense}/approval', [ExpenseApprovalController::class, 'show'])->name('expenses.approval.show');
    Route::post('expenses/{expense}/approval/start', [ExpenseApprovalController::class, 'startApproval'])->name('expenses.approval.start');
    Route::post('expenses/{expense}/approval/decision', [ExpenseApprovalController::class, 'processDecision'])->name('expenses.approval.decision');
    Route::get('expenses/{expense}/approval/can-approve', [ExpenseApprovalController::class, 'canApprove'])->name('expenses.approval.can-approve');

    // Dimension configuration routes
    Route::get('dimension-configurations', [DimensionConfigurationController::class, 'index'])->name('dimensions.configurations.index');
    Route::put('dimension-configurations', [DimensionConfigurationController::class, 'update'])->name('dimensions.configurations.update');
    Route::post('dimension-configurations/reset', [DimensionConfigurationController::class, 'resetToDefaults'])->name('dimensions.configurations.reset');
    Route::get('available-dimensions', [DimensionConfigurationController::class, 'availableDimensions'])->name('dimensions.available');

    Route::apiResource('expenses/{expense}/attachments', ExpenseAttachmentsController::class);
    Route::apiResource('expenses', ExpenseController::class);
});
