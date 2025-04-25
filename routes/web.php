<?php

use App\Domain\Auth\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello, world!',
    ]);
});

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login']);
Route::any('admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/debug-admin', function () {
    $user = Auth::guard('web')->user();

    if (!$user) {
        return response('Not logged in.', 403);
    }

    return [
        'id'                 => $user->id,
        'email'              => $user->email,
        'is_admin'           => $user->is_admin ?? false,
        'guard'              => Auth::getDefaultDriver(),
        'can_view_telescope' => app()->make(Illuminate\Contracts\Auth\Access\Gate::class)->check('viewTelescope'),
    ];
});
