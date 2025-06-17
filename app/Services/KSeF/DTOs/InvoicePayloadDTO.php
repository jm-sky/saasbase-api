<?php

namespace App\Services\KSeF\DTOs;

final class InvoicePayloadDTO
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $invoiceBody = null,
        public readonly ?string $encryptedInvoiceBody = null,
        public readonly ?InvoiceHashDTO $encryptedInvoiceHash = null
    ) {
    }
}
