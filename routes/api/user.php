<?php

use App\Domain\Auth\Controllers\AuthController;
use App\Domain\Auth\Controllers\UserProfileImageController;
use App\Domain\Auth\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('user')->group(function () {
    Route::get('profile', [AuthController::class, 'getUser']); // TODO: Move to UserController or sth.
    Route::get('settings', [UserSettingsController::class, 'show']);
    Route::put('settings', [UserSettingsController::class, 'update']);
    Route::patch('settings/language', [UserSettingsController::class, 'updateLanguage']);
    Route::post('profile-image', [UserProfileImageController::class, 'upload']);
    Route::get('profile-image', [UserProfileImageController::class, 'show']);
    Route::delete('profile-image', [UserProfileImageController::class, 'delete']);
});
