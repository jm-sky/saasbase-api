<?php

use App\Domain\Auth\Controllers\AuthController;
use App\Domain\Calendar\Http\Controllers\EventController;
use App\Domain\Common\Controllers\ActivityLogController;
use App\Domain\Common\Controllers\TagController;
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
        require __DIR__ . '/api/skills.php';
        require __DIR__ . '/api/admin.php';
        require __DIR__ . '/api/common_resources.php';

        Route::middleware('is_in_tenant')->group(function () {
            // TODO: move outside tenant middleware and check only for public users
            Route::apiResource('users', PublicUserController::class)->only(['index', 'show']);
            Route::get('users/search', [PublicUserController::class, 'search'])->name('users.search');

            require __DIR__ . '/api/contractors.php';
            require __DIR__ . '/api/projects.php';
            require __DIR__ . '/api/products.php';
            require __DIR__ . '/api/expenses.php';
            require __DIR__ . '/api/invoices.php';
            require __DIR__ . '/api/subscriptions.php';
            require __DIR__ . '/api/financial_reports.php';
            require __DIR__ . '/api/shared.php';

            Route::apiResource('tags', TagController::class)->only(['index', 'store']);
            Route::apiResource('events', EventController::class);
            Route::apiResource('roles', RoleController::class);

            Route::get('/logs', [ActivityLogController::class, 'index']);

            Route::apiResource('contacts', App\Domain\Common\Controllers\ContactController::class);
            Route::get('contacts/search', [App\Domain\Common\Controllers\ContactController::class, 'search'])->name('contacts.search');
        });
    });

    require __DIR__ . '/api/stripe.php';
});
