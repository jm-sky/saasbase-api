<?php

use App\Domain\Auth\Controllers\MeController;
use App\Domain\Auth\Controllers\UserIdentityController;
use App\Domain\Auth\Controllers\UserProfileController;
use App\Domain\Auth\Controllers\UserProfileImageController;
use App\Domain\Auth\Controllers\UserSettingsController;
use App\Domain\Users\Controllers\NotificationSettingController;
use App\Domain\Users\Controllers\SecurityEventController;
use App\Domain\Users\Controllers\TrustedDeviceController;
use App\Domain\Users\Controllers\UserTableSettingController;
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

// User Identity Routes
Route::prefix('user-identity')->group(function () {
    Route::post('personal-data', [UserIdentityController::class, 'storePersonalData']);
    Route::get('personal-data', [UserIdentityController::class, 'getPersonalData']);
    Route::post('documents', [UserIdentityController::class, 'storeIdentityDocument']);
    Route::get('documents', [UserIdentityController::class, 'getIdentityDocuments']);
    Route::get('documents/{document}', [UserIdentityController::class, 'getIdentityDocument']);
});

// Table settings routes
Route::prefix('table-settings')->group(function () {
    Route::get('/', [UserTableSettingController::class, 'index']);
    Route::post('/', [UserTableSettingController::class, 'store']);
    Route::put('/{setting}', [UserTableSettingController::class, 'update']);
    Route::delete('/{setting}', [UserTableSettingController::class, 'destroy']);
    Route::post('/{setting}/default', [UserTableSettingController::class, 'setDefault']);
});

// Notification settings routes
Route::prefix('notification-settings')->group(function () {
    Route::get('/', [NotificationSettingController::class, 'index']);
    Route::put('/', [NotificationSettingController::class, 'update']);
    Route::put('/bulk', [NotificationSettingController::class, 'updateBulk']);
});

// Trusted devices routes
Route::prefix('trusted-devices')->group(function () {
    Route::get('/', [TrustedDeviceController::class, 'index']);
    Route::delete('/{device}', [TrustedDeviceController::class, 'destroy']);
    Route::delete('/', [TrustedDeviceController::class, 'destroyAll']);
});

// Security events routes
Route::prefix('security-events')->group(function () {
    Route::get('/', [SecurityEventController::class, 'index']);
    Route::get('/{event}', [SecurityEventController::class, 'show']);
});
