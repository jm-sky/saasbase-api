<?php

namespace App\Services\RegonLookup\DTOs;

final class RegonAuthResultDTO
{
    public function __construct(
        public readonly string $sessionKey
    ) {
    }
}
