<?php

use App\Domain\Invoice\Controllers\InvoiceAttachmentsController;
use App\Domain\Invoice\Controllers\InvoiceController;
use App\Domain\Invoice\Controllers\InvoiceShareTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::get('invoices/search', [InvoiceController::class, 'search'])->name('invoices.search');
    Route::get('invoices/export', [InvoiceController::class, 'export']);

    // Invoice PDF generation routes
    Route::post('invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf.generate');
    Route::get('invoices/{invoice}/pdf/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf.download');
    Route::get('invoices/{invoice}/pdf/stream', [InvoiceController::class, 'streamPdf'])->name('invoices.pdf.stream');
    Route::post('invoices/{invoice}/pdf/attach', [InvoiceController::class, 'attachPdf'])->name('invoices.pdf.attach');
    Route::get('invoices/{invoice}/pdf/preview', [InvoiceController::class, 'previewPdf'])->name('invoices.pdf.preview');

    Route::get('invoices/{invoice}/attachments/{media}/download', [InvoiceAttachmentsController::class, 'download'])->name('invoices.attachments.download');
    Route::get('invoices/{invoice}/attachments/{media}/preview', [InvoiceAttachmentsController::class, 'preview'])->name('invoices.attachments.preview');
    Route::apiResource('invoices/{invoice}/attachments', InvoiceAttachmentsController::class);
    Route::apiResource('invoices', InvoiceController::class);

    Route::apiResource('invoices.share-tokens', InvoiceShareTokenController::class)->only(['index', 'store', 'destroy']);
});
