<?php

namespace App\Services\KSeF\DTOs;

final class InitSessionRequestDTO
{
    public function __construct(
        public readonly string $encryptedToken
    ) {
    }
}
