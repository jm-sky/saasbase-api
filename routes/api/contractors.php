<?php

use App\Domain\Contractors\Controllers\ContractorActivityLogController;
use App\Domain\Contractors\Controllers\ContractorAddressController;
use App\Domain\Contractors\Controllers\ContractorAttachmentsController;
use App\Domain\Contractors\Controllers\ContractorBankAccountController;
use App\Domain\Contractors\Controllers\ContractorCommentsController;
use App\Domain\Contractors\Controllers\ContractorContactController;
use App\Domain\Contractors\Controllers\ContractorController;
use App\Domain\Contractors\Controllers\ContractorLogoController;
use App\Domain\Contractors\Controllers\ContractorTagsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::apiResource('contractors', ContractorController::class);

    Route::controller(ContractorLogoController::class)
        ->prefix('contractors/{contractor}/logo')
        ->name('contractors.logo.')
        ->group(function () {
            Route::post('/', 'upload')->name('upload');
            Route::delete('/', 'show')->name('show');
            Route::delete('/', 'delete')->name('delete');
        })
    ;

    Route::apiResource('contractors/{contractor}/addresses', ContractorAddressController::class)->names('contractors.addresses');
    Route::post('contractors/{contractor}/addresses/{address}/set-default', [ContractorAddressController::class, 'setDefault'])
        ->name('contractors.addresses.setDefault')
    ;

    Route::apiResource('contractors/{contractor}/bank-accounts', ContractorBankAccountController::class)->names('contractors.bankAccounts');
    Route::post('contractors/{contractor}/bank-accounts/{bankAccount}/set-default', [ContractorBankAccountController::class, 'setDefault'])
        ->name('contractors.bankAccounts.setDefault')
    ;

    Route::apiResource('contractors/{contractor}/contacts', ContractorContactController::class)->names('contractors.contacts');

    Route::controller(ContractorAttachmentsController::class)
        ->prefix('contractors/{contractor}/attachments')
        ->name('contractors.attachments.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{media}', 'show')->name('show');
            Route::get('{media}/download', 'download')->name('download');
            Route::get('{media}/preview', 'preview')->name('preview');
            Route::delete('{media}', 'destroy')->name('destroy');
        })
    ;

    Route::prefix('contractors/{contractor}/tags')
        ->name('contractors.tags.')
        ->group(function () {
            Route::get('/', [ContractorTagsController::class, 'index'])->name('index');
            Route::post('/', [ContractorTagsController::class, 'store'])->name('store');
            Route::patch('/', [ContractorTagsController::class, 'sync'])->name('sync');
            Route::delete('{tag}', [ContractorTagsController::class, 'destroy'])->name('destroy');
        })
    ;

    Route::apiResource('contractors/{contractor}/comments', ContractorCommentsController::class)->names('contractors.comments');

    Route::get('/{contractor}/logs', [ContractorActivityLogController::class, 'index']);
});
