<?php

use App\Domain\Common\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ActivityLogController::class, 'index']);
