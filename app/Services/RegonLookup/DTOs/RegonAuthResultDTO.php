<?php

namespace App\Services\RegonLookup\DTOs;

class RegonAuthResultDTO
{
    public function __construct(
        public readonly string $sessionKey
    ) {
    }
}
