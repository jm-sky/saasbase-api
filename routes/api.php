<?php

use App\Domain\Auth\Controllers\AuthController;
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

Route::post('/v1/auth/login', [AuthController::class, 'login']);
Route::post('/v1/auth/register', [AuthController::class, 'register']);

Route::prefix('v1')->group(function () {
    Route::apiResource('tenants', TenantController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('contractors', ContractorController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('skills', SkillController::class);
        Route::apiResource('skill-categories', SkillCategoryController::class);
        Route::apiResource('user-skills', UserSkillController::class);
        Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
        Route::get('user/settings', [UserSettingsController::class, 'show']);
        Route::put('user/settings', [UserSettingsController::class, 'update']);
        Route::patch('user/settings/language', [UserSettingsController::class, 'updateLanguage']);
    });

    Route::middleware('auth:api')->group(function () {
        Route::post('tenants/{tenant}/switch', GenerateTenantJwtAction::class)->name('tenant.switch');
    });
});
