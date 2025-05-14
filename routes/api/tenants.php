<?php

use App\Domain\Tenant\Actions\GenerateTenantJwtAction;
use App\Domain\Tenant\Controllers\InvitationController;
use App\Domain\Tenant\Controllers\TenantAttachmentsController;
use App\Domain\Tenant\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/switch', GenerateTenantJwtAction::class)->name('tenant.switch');
});

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
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
Route::get('invitations/{token}', [InvitationController::class, 'accept']);
