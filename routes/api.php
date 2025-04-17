<?php

use App\Domain\Contractors\Controllers\ContractorController;
use App\Domain\Products\Controllers\ProductController;
use App\Domain\Skills\Controllers\{SkillController, SkillCategoryController, UserSkillController};
use App\Domain\Common\Controllers\CountryController;
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
    Route::apiResource('tenants', TenantController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('contractors', ContractorController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('skills', SkillController::class);
        Route::apiResource('skill-categories', SkillCategoryController::class);
        Route::apiResource('user-skills', UserSkillController::class);
        Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
    });
});
