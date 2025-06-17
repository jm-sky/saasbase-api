<?php

namespace App\Services\KSeF\DTOs;

final class SessionTokenDTO
{
    public function __construct(
        public readonly string $token,
        public readonly SessionContextDTO $context
    ) {
    }
}
