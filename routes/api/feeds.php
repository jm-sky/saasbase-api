<?php

use App\Domain\Feeds\Controllers\FeedCommentController;
use App\Domain\Feeds\Controllers\FeedController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::get('/feeds', [FeedController::class, 'index']);
    Route::post('/feeds', [FeedController::class, 'store']);
    Route::get('/feeds/{feed}', [FeedController::class, 'show']);
    Route::delete('/feeds/{feed}', [FeedController::class, 'destroy']);

    Route::post('/feeds/{feed}/comments', [FeedCommentController::class, 'store']);
    Route::delete('/feed-comments/{comment}', [FeedCommentController::class, 'destroy']);
});
