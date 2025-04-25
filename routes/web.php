<?php

use App\Domain\Auth\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return response()->json([
        'message' => 'Hello, world!',
    ]);
});

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login']);
Route::any('admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('admin/logs', 
[\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
