<?php

use App\Domain\Tenant\Actions\GenerateTenantJwtAction;
use App\Domain\Tenant\Controllers\InvitationController;
use App\Domain\Tenant\Controllers\TenantAddressController;
use App\Domain\Tenant\Controllers\TenantAttachmentsController;
use App\Domain\Tenant\Controllers\TenantBankAccountController;
use App\Domain\Tenant\Controllers\TenantController;
use App\Domain\Tenant\Controllers\TenantLogoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/switch', GenerateTenantJwtAction::class)->name('tenant.switch');
});

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::controller(TenantLogoController::class)
        ->prefix('tenants/{tenant}/logo')
        ->name('tenants.logo.')
        ->group(function () {
            Route::post('/', 'upload')->name('upload');
            Route::delete('/', 'show')->name('show');
            Route::delete('/', 'delete')->name('delete');
        })
    ;

    Route::apiResource('tenants/{tenant}/addresses', TenantAddressController::class)->name('tenants.');
    Route::post('tenants/{tenant}/addresses/{address}/set-default', [TenantAddressController::class, 'setDefault'])
        ->name('tenants.addresses.setDefault')
    ;

    Route::apiResource('tenants/{tenant}/bank-accounts', TenantBankAccountController::class)->name('tenants.');
    Route::post('tenants/{tenant}/bank-accounts/{bankAccount}/set-default', [TenantBankAccountController::class, 'setDefault'])
        ->name('tenants.bankAccounts.setDefault')
    ;

    Route::controller(TenantAttachmentsController::class)
        ->prefix('tenants/{tenant}/attachments')
        ->name('tenants.attachments.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{media}', 'show')->name('show');
            Route::get('{media}/download', 'download')->name('download');
            Route::get('{media}/preview', 'preview')->name('preview');
            Route::delete('{media}', 'destroy')->name('destroy');
        })
    ;

    // Invitation routes
    Route::post('tenants/{tenant}/invite', [InvitationController::class, 'send']);
});

// Accept invitation (public, no auth required)
Route::withoutMiddleware(['auth:api', 'is_active', 'is_in_tenant'])
    ->get('invitations/{token}', [InvitationController::class, 'accept'])
;
