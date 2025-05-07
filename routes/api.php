<?php

use App\Domain\Auth\Controllers\AuthController;
use App\Domain\Common\Controllers\CountryController;
use App\Domain\Contractors\Controllers\ContractorAddressController;
use App\Domain\Contractors\Controllers\ContractorController;
use App\Domain\Exchanges\Controllers\ExchangeController;
use App\Domain\Products\Controllers\ProductController;
use App\Domain\Users\Controllers\PublicUserController;
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

        require __DIR__ . '/api/user.php';
        require __DIR__ . '/api/tenants.php';
        require __DIR__ . '/api/feeds.php';

        Route::apiResource('countries', CountryController::class)->only(['index', 'show']);

        Route::middleware(['is_active'])->group(function () {
            Route::apiResource('contractors', ContractorController::class);
            Route::apiResource('contractors/{contractor}/addresses', ContractorAddressController::class);
            Route::apiResource('products', ProductController::class);
            Route::apiResource('users', PublicUserController::class)->only(['index', 'show']);

            Route::apiResource('exchanges', ExchangeController::class)->only(['index', 'show']);
            Route::get('exchanges/{exchange}/rates', [ExchangeController::class, 'getRates']);

            require __DIR__ . '/api/projects.php';
            require __DIR__ . '/api/skills.php';
        });
    });

    require __DIR__ . '/api/admin.php';
});
