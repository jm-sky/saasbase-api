<?php

use App\Domain\Common\Controllers\MeasurementUnitController;
use App\Domain\Financial\Controllers\GtuCodeController;
use App\Domain\Financial\Controllers\PaymentMethodController;
use App\Domain\Financial\Controllers\PKWiUClassificationController;
use App\Domain\Financial\Controllers\VatRateController;
use App\Domain\Invoice\Controllers\NumberingTemplateController;
use App\Domain\Template\Controllers\InvoiceTemplateController;
use App\Domain\Tenant\Controllers\PositionCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::apiResource('vat-rates', VatRateController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('measurement-units', MeasurementUnitController::class)->only(['index', 'store', 'destroy']);

    Route::get('pkwiu/search', [PKWiUClassificationController::class, 'search'])->name('pkwiu.search');
    Route::get('pkwiu/tree', [PKWiUClassificationController::class, 'tree'])->name('pkwiu.tree');
    Route::get('pkwiu/tree', [PKWiUClassificationController::class, 'tree'])->name('pkwiu.tree');
    Route::apiResource('pkwiu', PKWiUClassificationController::class)->only(['index', 'show']);

    Route::apiResource('gtu', GtuCodeController::class)->only(['index', 'show']);

    Route::post('numbering-templates/preview', [NumberingTemplateController::class, 'preview'])->name('numbering-templates.preview');
    Route::post('numbering-templates/{numbering_template}/set-default', [NumberingTemplateController::class, 'setDefault']);
    Route::apiResource('numbering-templates', NumberingTemplateController::class)
        ->only(['index', 'store', 'update', 'destroy'])
    ;

    Route::apiResource('payment-methods', PaymentMethodController::class)->only(['index', 'store', 'destroy']);

    Route::apiResource('invoice-templates', InvoiceTemplateController::class);
    Route::apiResource('position-categories', PositionCategoryController::class)
        ->only(['index', 'store', 'update', 'destroy'])
    ;
});
