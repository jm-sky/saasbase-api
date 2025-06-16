<?php

namespace App\Services\KSeF\DTOs;

class ContextIdentifierDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $identifier
    ) {
    }
}
