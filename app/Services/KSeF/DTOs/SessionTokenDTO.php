<?php

namespace App\Services\KSeF\DTOs;

class SessionTokenDTO
{
    public function __construct(
        public readonly string $token,
        public readonly SessionContextDTO $context
    ) {
    }
}
