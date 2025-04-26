<?php

use App\Domain\Auth\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello, world!',
    ]);
});

Route::prefix('admin')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::any('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::middleware('auth:web')->group(function () {
      Route::get('logs', [LogViewerController::class, 'index']);
    });
});
