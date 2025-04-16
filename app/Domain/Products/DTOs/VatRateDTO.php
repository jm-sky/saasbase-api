<?php

namespace App\Domain\Products\DTOs;

use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property float $rate
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class VatRateDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly float $rate,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
