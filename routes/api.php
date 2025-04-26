<?php

use App\Domain\Auth\Controllers\AuthController;
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
    require __DIR__ . '/api/auth.php';

    Route::middleware('auth:api')->group(function () {
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('user', [AuthController::class, 'getUser']);

        require __DIR__ . '/api/user.php';

        Route::apiResource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/switch', GenerateTenantJwtAction::class)->name('tenant.switch');

        Route::apiResource('contractors', ContractorController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('skills', SkillController::class);
        Route::apiResource('skill-categories', SkillCategoryController::class);
        Route::apiResource('user-skills', UserSkillController::class);
        Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
    });

    require __DIR__ . '/api/admin.php';
});
