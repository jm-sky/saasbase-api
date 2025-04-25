<?php

use App\Domain\Auth\Controllers\AuthController;


use App\Domain\Auth\Controllers\OAuthController;

use App\Domain\Auth\Controllers\UserProfileImageController;

use App\Domain\Auth\Controllers\UserSettingsController;
use App\Domain\Common\Controllers\CountryController;
use App\Domain\Contractors\Controllers\ContractorController;
use App\Domain\Products\Controllers\ProductController;
use App\Domain\Skills\Controllers\SkillCategoryController;
use App\Domain\Skills\Controllers\SkillController;
use App\Domain\Skills\Controllers\UserSkillController;
use App\Domain\Tenant\Actions\GenerateTenantJwtAction;
use App\Domain\Tenant\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('register', [AuthController::class, 'register']);
    });

    Route::prefix('oauth')->group(function () {
        Route::get('{provider}/redirect', [OAuthController::class, 'redirect']);
        Route::get('{provider}/callback', [OAuthController::class, 'callback']);
    });

    Route::apiResource('tenants', TenantController::class);

    Route::middleware('auth:api')->group(function () {
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('user', [AuthController::class, 'getUser']);

        Route::prefix('user')->group(function () {
            Route::get('settings', [UserSettingsController::class, 'show']);
            Route::put('settings', [UserSettingsController::class, 'update']);
            Route::patch('settings/language', [UserSettingsController::class, 'updateLanguage']);
            Route::post('profile-image', [UserProfileImageController::class, 'upload']);
            Route::get('profile-image', [UserProfileImageController::class, 'show']);
            Route::delete('profile-image', [UserProfileImageController::class, 'delete']);
        });

        Route::apiResource('contractors', ContractorController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('skills', SkillController::class);
        Route::apiResource('skill-categories', SkillCategoryController::class);
        Route::apiResource('user-skills', UserSkillController::class);
        Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
    });

    Route::middleware('auth:api')->group(function () {
        Route::post('tenants/{tenant}/switch', GenerateTenantJwtAction::class)->name('tenant.switch');
    });
});
