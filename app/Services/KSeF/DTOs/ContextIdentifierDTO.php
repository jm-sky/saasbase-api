<?php

namespace App\Services\KSeF\DTOs;

final class ContextIdentifierDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $identifier
    ) {
    }
}
