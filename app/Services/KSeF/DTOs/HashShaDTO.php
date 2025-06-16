<?php

namespace App\Services\KSeF\DTOs;

class HashShaDTO
{
    public function __construct(
        public readonly string $algorithm,
        public readonly string $encoding,
        public readonly string $value
    ) {
    }
}
