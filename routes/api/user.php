<?php

use App\Domain\Auth\Controllers\MeController;
use App\Domain\Auth\Controllers\UserProfileController;
use App\Domain\Auth\Controllers\UserProfileImageController;
use App\Domain\Auth\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['auth:api', 'is_active'])
    ->get('user/profile-image/{user}', [UserProfileImageController::class, 'showForUser'])
    ->name('user.profile-image.showForUser')
;

Route::middleware('auth:api')->get('me', MeController::class);

Route::middleware('auth:api')->prefix('user')->group(function () {
    Route::put('profile', [UserProfileController::class, 'update']);
    Route::get('settings', [UserSettingsController::class, 'show']);
    Route::put('settings', [UserSettingsController::class, 'update']);
    Route::patch('settings/language', [UserSettingsController::class, 'updateLanguage']);
    Route::post('profile-image', [UserProfileImageController::class, 'upload'])->name('user.profile-image.upload');
    Route::delete('profile-image', [UserProfileImageController::class, 'delete'])->name('user.profile-image.delete');
    Route::get('profile-image', [UserProfileImageController::class, 'show'])->name('user.profile-image.show');
});
