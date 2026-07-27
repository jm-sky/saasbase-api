<?php

use App\Domain\Invoice\Controllers\PublicSharedInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('shared/invoices/{token}', [PublicSharedInvoiceController::class, 'show'])
    ->where('token', '[A-Za-z0-9]+');
