<?php

namespace App\Services\GusLookup\DTOs;

class GusAuthResultDTO
{
    public function __construct(
        public readonly string $sessionKey,
    ) {
    }
}
