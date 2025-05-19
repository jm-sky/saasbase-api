<?php

use App\Domain\Tenant\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;

// Accept invitation (public, no auth required)
Route::get('invitations/{token}', [InvitationController::class, 'show']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('invitations/{token}/accept', [InvitationController::class, 'accept']);
    Route::post('invitations/{token}/reject', [InvitationController::class, 'reject']);
});
