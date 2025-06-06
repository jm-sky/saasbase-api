<?php

use App\Domain\Products\Controllers\ProductActivityLogController;
use App\Domain\Products\Controllers\ProductAttachmentsController;
use App\Domain\Products\Controllers\ProductCommentsController;
use App\Domain\Products\Controllers\ProductController;
use App\Domain\Products\Controllers\ProductLogoController;
use App\Domain\Products\Controllers\ProductTagsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');

    Route::controller(ProductLogoController::class)
        ->prefix('products/{product}/logo')
        ->name('products.logo.')
        ->group(function () {
            Route::post('/', 'upload')->name('upload');
            Route::get('/', 'show')->name('show');
            Route::delete('/', 'delete')->name('delete');
        })
    ;

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

    Route::prefix('products/{product}/tags')
        ->name('products.tags.')
        ->group(function () {
            Route::get('/', [ProductTagsController::class, 'index'])->name('index');
            Route::post('/', [ProductTagsController::class, 'store'])->name('store');
            Route::patch('/', [ProductTagsController::class, 'sync'])->name('sync');
            Route::delete('{tag}', [ProductTagsController::class, 'destroy'])->name('destroy');
        })
    ;

    Route::apiResource('products.comments', ProductCommentsController::class)->names('products.comments');

    Route::get('/products/{product}/logs', [ProductActivityLogController::class, 'index']);
});
