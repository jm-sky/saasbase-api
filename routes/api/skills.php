<?php

use App\Domain\Skills\Controllers\SkillCategoryController;
use App\Domain\Skills\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::apiResource('skills', SkillController::class);
    Route::apiResource('skill-categories', SkillCategoryController::class);
});
