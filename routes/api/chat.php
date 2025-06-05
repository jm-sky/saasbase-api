<?php

use App\Domain\Ai\Controllers\AiChatController;
use App\Domain\Chat\Controllers\DirectMessageController;
use App\Domain\Chat\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('chat/rooms', [DirectMessageController::class, 'listRooms']);
    Route::post('chat/rooms', [DirectMessageController::class, 'createRoom']);
    Route::post('chat/rooms/{room}/messages', [MessageController::class, 'sendMessage']);
    Route::get('chat/rooms/{room}/messages', [MessageController::class, 'listMessages']);

    Route::post('ai/chat', [AiChatController::class, 'chat']);
    Route::post('ai/chat/stop', [AiChatController::class, 'stopStreaming']);
});
