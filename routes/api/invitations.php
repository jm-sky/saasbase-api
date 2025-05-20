<?php

use App\Domain\Auth\Controllers\ApplicationInvitationController;
use App\Domain\Tenant\Controllers\TenantInvitationController;
use Illuminate\Support\Facades\Route;

// Accept invitation (public, no auth required)
Route::get('tenant/invitations/{token}', [TenantInvitationController::class, 'show']);
Route::get('application/invitations/{token}', [ApplicationInvitationController::class, 'show']);

Route::middleware(['auth:api'])->group(function () {
    // Tenant invitations
    Route::prefix('tenants/{tenant}/invitations')->group(function () {
        Route::post('/', [TenantInvitationController::class, 'send']);
        Route::get('/', [TenantInvitationController::class, 'index']);
        Route::delete('/{invitation}', [TenantInvitationController::class, 'cancel']);
        Route::post('/{invitation}/resend', [TenantInvitationController::class, 'resend']);
        Route::post('/{token}/accept', [TenantInvitationController::class, 'accept']);
        Route::post('/{token}/reject', [TenantInvitationController::class, 'reject']);
    });

    // Application invitations
    Route::prefix('application/invitations')->group(function () {
        Route::post('/', [ApplicationInvitationController::class, 'send']);
        Route::get('/', [ApplicationInvitationController::class, 'index']);
        Route::delete('/{invitation}', [ApplicationInvitationController::class, 'cancel']);
        Route::post('/{invitation}/resend', [ApplicationInvitationController::class, 'resend']);
        Route::post('/{token}/accept', [ApplicationInvitationController::class, 'accept']);
        Route::post('/{token}/reject', [ApplicationInvitationController::class, 'reject']);
    });
});
