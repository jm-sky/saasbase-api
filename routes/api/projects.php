<?php

use App\Domain\Projects\Controllers\ProjectAttachmentsController;
use App\Domain\Projects\Controllers\ProjectController;
use App\Domain\Projects\Controllers\ProjectStatusController;
use App\Domain\Projects\Controllers\TaskAttachmentsController;
use App\Domain\Projects\Controllers\TaskController;
use App\Domain\Projects\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('project-statuses', ProjectStatusController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('task-statuses', TaskStatusController::class);

    Route::controller(ProjectAttachmentsController::class)
        ->prefix('projects/{project}/attachments')
        ->name('projects.attachments.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{media}', 'show')->name('show');
            Route::get('{media}/download', 'download')->name('download');
            Route::get('{media}/preview', 'preview')->name('preview');
            Route::delete('{media}', 'destroy')->name('destroy');
        })
    ;

    Route::controller(TaskAttachmentsController::class)
        ->prefix('tasks/{task}/attachments')
        ->name('tasks.attachments.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{media}', 'show')->name('show');
            Route::get('{media}/download', 'download')->name('download');
            Route::get('{media}/preview', 'preview')->name('preview');
            Route::delete('{media}', 'destroy')->name('destroy');
        })
    ;
});
