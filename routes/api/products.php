<?php

use App\Domain\Products\Controllers\ProductAttachmentsController;
use App\Domain\Products\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::apiResource('products', ProductController::class);

    Route::controller(ProductAttachmentsController::class)
        ->prefix('products/{product}/attachments')
        ->name('products.attachments.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{media}', 'show')->name('show');
            Route::get('{media}/download', 'download')->name('download');
            Route::get('{media}/preview', 'preview')->name('preview');
            Route::delete('{media}', 'destroy')->name('destroy');
        })
    ;
});
