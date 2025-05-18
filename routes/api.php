<?php

use App\Domain\Auth\Controllers\AuthController;
use App\Domain\Common\Controllers\ActivityLogController;
use App\Domain\Tenant\Controllers\InvitationController;
use App\Domain\Common\Controllers\CountryController;
use App\Domain\Exchanges\Controllers\ExchangeController;
use App\Domain\Tenant\Controllers\TenantActivityLogController;
use App\Domain\Users\Controllers\PublicUserController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Broadcast;
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

Broadcast::routes([
    'middleware' => ['auth:api'],
    'prefix'     => 'v1',
    'as'         => 'broadcast.',
]);

Route::get('/health', [HealthController::class, 'health']);

Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/auth.php';

    // Accept invitation (public, no auth required)
    Route::get('invitations/{token}', [InvitationController::class, 'show']);
    Route::post('invitations/{token}', [InvitationController::class, 'accept']);
    
    Route::post('auth/token/refresh', [AuthController::class, 'refresh']);

    require __DIR__ . '/api/images.php';

    Route::middleware(['auth:api', 'is_active'])->group(function () {
        require __DIR__ . '/api/user.php';
        require __DIR__ . '/api/tenants.php';
        require __DIR__ . '/api/feeds.php';
        require __DIR__ . '/api/chat.php';

        Route::apiResource('countries', CountryController::class)->only(['index', 'show']);

        Route::middleware('is_in_tenant')->group(function () {
            // TODO: move outside tenant middleware and check only for public users
            Route::apiResource('users', PublicUserController::class)->only(['index', 'show']);

            require __DIR__ . '/api/projects.php';
            require __DIR__ . '/api/contractors.php';
            require __DIR__ . '/api/products.php';

            // Activity logs
            Route::get('/logs', [ActivityLogController::class, 'index']);
        });

        Route::apiResource('exchanges', ExchangeController::class)->only(['index', 'show']);
        Route::get('exchanges/{exchange}/rates', [ExchangeController::class, 'getRates']);

        require __DIR__ . '/api/skills.php';
    });

    require __DIR__ . '/api/admin.php';

    Route::post('ai/chat', [App\Domain\Ai\Controllers\AiChatController::class, 'chat']);
});
