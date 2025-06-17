<?php

namespace App\Services\KSeF\DTOs;

final class ContextNameDTO
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $tradeName,
        public readonly string $fullName
    ) {
    }
}
