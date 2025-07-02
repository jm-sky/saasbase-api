<?php

use App\Domain\Tenant\Actions\GenerateTenantJwtAction;
use App\Domain\Tenant\Controllers\OrganizationUnitController;
use App\Domain\Tenant\Controllers\TenantActivityLogController;
use App\Domain\Tenant\Controllers\TenantAddressController;
use App\Domain\Tenant\Controllers\TenantAttachmentsController;
use App\Domain\Tenant\Controllers\TenantBankAccountController;
use App\Domain\Tenant\Controllers\TenantBrandingController;
use App\Domain\Tenant\Controllers\TenantController;
use App\Domain\Tenant\Controllers\TenantIntegrationController;
use App\Domain\Tenant\Controllers\TenantLogoController;
use App\Domain\Tenant\Controllers\TenantPublicProfileController;
use App\Domain\Tenant\Controllers\TenantSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('tenants/preview', [TenantController::class, 'indexPreview'])->name('tenants.preview');
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

    Route::apiResource('tenants/{tenant}/organization-units', OrganizationUnitController::class)->names('tenants.organizationUnits');

    Route::apiResource('tenants/{tenant}/addresses', TenantAddressController::class)->names('tenants.addresses');
    Route::post('tenants/{tenant}/addresses/{address}/set-default', [TenantAddressController::class, 'setDefault'])
        ->name('tenants.addresses.setDefault')
    ;

    Route::apiResource('tenants/{tenant}/bank-accounts', TenantBankAccountController::class)->names('tenants.bankAccounts');
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

    // Tenant Branding
    Route::get('/tenants/{tenant}/branding', [TenantBrandingController::class, 'show']);
    Route::put('/tenants/{tenant}/branding', [TenantBrandingController::class, 'update']);
    Route::delete('/tenants/{tenant}/branding/{collection}', [TenantBrandingController::class, 'deleteMedia']);

    // Tenant Public Profile
    Route::get('/tenants/{tenant}/public-profile', [TenantPublicProfileController::class, 'show']);
    Route::put('/tenants/{tenant}/public-profile', [TenantPublicProfileController::class, 'update']);
    Route::delete('/tenants/{tenant}/public-profile/{collection}', [TenantPublicProfileController::class, 'deleteMedia']);

    Route::get('tenants/{tenant}/logs', [TenantActivityLogController::class, 'index']);

    Route::get('tenants/{tenant}/quota', [TenantSubscriptionController::class, 'quota'])->name('tenants.quota');
    Route::get('tenants/{tenant}/current-plan', [TenantSubscriptionController::class, 'currentPlan'])->name('tenants.currentPlan');

    Route::apiResource('tenants/{tenant}/integrations', TenantIntegrationController::class)->names('tenants.integrations');
});
