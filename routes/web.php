<?php

use App\Domain\Auth\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;


Route::get('/', function () {
    return response()->json([
        'message' => 'Hello, world!',
    ]);
});

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login']);
Route::any('admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/debug-admin', function () {
    $userWeb = Auth::guard('web')->user();
    $userApi = Auth::guard('api')->user();

    return response()->json([
        'web_user' => $userWeb?->only(['id', 'email', 'is_admin']),
        'api_user' => $userApi?->only(['id', 'email', 'is_admin']),
        'default_guard' => Auth::getDefaultDriver(),
        'web_guard_check' => Auth::guard('web')->check(),
        'can_view_telescope' => Gate::check('viewTelescope'),
        'session_id' => Session::getId(),
        'session_data' => Session::all(),
    ]);
});
