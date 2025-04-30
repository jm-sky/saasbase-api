<?php

use App\Domain\Projects\Controllers\ProjectController;
use App\Domain\Projects\Controllers\ProjectStatusController;
use App\Domain\Projects\Controllers\TaskController;
use App\Domain\Projects\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('project-statuses', ProjectStatusController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('task-statuses', TaskStatusController::class);
});
