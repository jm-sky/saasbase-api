<?php

use App\Domain\Common\Controllers\SignedFileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Images API Routes
|--------------------------------------------------------------------------
|
| Public but signedroutes for images.
|
*/

Route::withoutMiddleware(['api', 'auth:api', 'is_active', 'is_in_tenant'])
    ->get('/images/{modelName}/{modelId}/media/{mediaId}/{fileName}', [SignedFileController::class, 'show'])
    ->name('images.show')
    // ->middleware('signed')
;
