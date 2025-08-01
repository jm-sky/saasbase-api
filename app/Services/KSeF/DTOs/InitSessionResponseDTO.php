<?php

namespace App\Services\KSeF\DTOs;

use Carbon\Carbon;

final class InitSessionResponseDTO
{
    public function __construct(
        public readonly Carbon $timestamp,
        public readonly string $referenceNumber,
        public readonly SessionTokenDTO $sessionToken
    ) {
    }
}
