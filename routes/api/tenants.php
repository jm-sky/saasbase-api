<?php

use App\Domain\Tenant\Actions\GenerateTenantJwtAction;
use App\Domain\Tenant\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/switch', GenerateTenantJwtAction::class)->name('tenant.switch');
});
