<?php

namespace App\Services\KSeF\DTOs;

class SendInvoiceRequestDTO
{
    public function __construct(
        public readonly InvoiceHashDTO $invoiceHash,
        public readonly InvoicePayloadDTO $invoicePayload
    ) {
    }
}
