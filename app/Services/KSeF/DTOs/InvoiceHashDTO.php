<?php

namespace App\Services\KSeF\DTOs;

class InvoiceHashDTO
{
    public function __construct(
        public readonly HashShaDTO $hashSHA,
        public readonly int $fileSize
    ) {
    }
}
