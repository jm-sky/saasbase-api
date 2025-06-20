<?php

namespace App\Domain\Utils\DTOs;

class CompanyContext
{
    public function __construct(
        public ?string $nip,
        public ?string $regon,
        public ?string $country,
        public bool $force = false,
    ) {
    }
}
