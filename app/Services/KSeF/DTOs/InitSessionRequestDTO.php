<?php

namespace App\Services\KSeF\DTOs;

class InitSessionRequestDTO
{
    public function __construct(
        public readonly string $encryptedToken
    ) {
    }
}
