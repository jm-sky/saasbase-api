<?php

use App\Domain\Auth\Controllers\ApiKeyController;
use App\Domain\Auth\Controllers\AuthController;
use App\Domain\Calendar\Http\Controllers\EventController;
use App\Domain\Common\Controllers\ActivityLogController;
use App\Domain\Common\Controllers\CountryController;
use App\Domain\Common\Controllers\TagController;
use App\Domain\Exchanges\Controllers\ExchangeController;
use App\Domain\Invoice\Controllers\InvoiceController;
use App\Domain\Rights\Controllers\RoleController;
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
    Route::post('auth/token/refresh', [AuthController::class, 'refresh']);

    require __DIR__ . '/api/auth.php';
    require __DIR__ . '/api/invitations.php';
    require __DIR__ . '/api/images.php';
    require __DIR__ . '/api/user.php';
    require __DIR__ . '/api/utils.php';

    Route::middleware(['auth:api', 'is_active'])->group(function () {
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
            require __DIR__ . '/api/subscriptions.php';

            Route::apiResource('tags', TagController::class)->only(['index']);
            Route::apiResource('invoices', InvoiceController::class);
            Route::apiResource('events', EventController::class);
            Route::apiResource('roles', RoleController::class);
            Route::apiResource('api-keys', ApiKeyController::class);
            Route::get('/logs', [ActivityLogController::class, 'index']);
        });

        Route::apiResource('exchanges', ExchangeController::class)->only(['index', 'show']);
        Route::get('exchanges/{exchange}/rates', [ExchangeController::class, 'getRates']);

        require __DIR__ . '/api/skills.php';
    });

    require __DIR__ . '/api/admin.php';

    Route::post('ai/chat', [App\Domain\Ai\Controllers\AiChatController::class, 'chat']);

    // User Identity Routes
    Route::prefix('user-identity')->group(function () {
        Route::post('personal-data', [App\Domain\Auth\Controllers\UserIdentityController::class, 'storePersonalData']);
        Route::get('personal-data', [App\Domain\Auth\Controllers\UserIdentityController::class, 'getPersonalData']);
        Route::post('documents', [App\Domain\Auth\Controllers\UserIdentityController::class, 'storeIdentityDocument']);
        Route::get('documents', [App\Domain\Auth\Controllers\UserIdentityController::class, 'getIdentityDocuments']);
        Route::get('documents/{document}', [App\Domain\Auth\Controllers\UserIdentityController::class, 'getIdentityDocument']);
    });

    // Stripe Webhook
    Route::post('stripe/webhook', App\Domain\Subscription\Controllers\StripeWebhookController::class)
        ->name('stripe.webhook')
        ->middleware('stripe.webhook')
    ;
});
