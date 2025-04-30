<?php

use App\Domain\Auth\Controllers\AuthController;
use App\Domain\Auth\Controllers\OAuthController;
use App\Domain\Auth\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::prefix('oauth')->group(function () {
    Route::get('{provider}/redirect', [OAuthController::class, 'redirect']);
    Route::get('{provider}/callback', [OAuthController::class, 'callback']);
});

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->name('verification.verify')
;

Route::post('/email/verification-notification', [VerifyEmailController::class, 'resend'])
    ->middleware(['auth:api', 'throttle:6,1'])
    ->name('verification.send')
;
